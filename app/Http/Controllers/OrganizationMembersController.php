<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrganizationInvite;
use App\Mail\OrganizationInviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrganizationMembersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        abort_unless($org, 404);

        // Must be a member of org
        abort_unless($org->users()->where('users.id', $user->id)->exists(), 403);

        $myRole = $user->orgRole($org->id) ?? 'member';

        $members = $org->users()
            ->select('users.id', 'users.name', 'users.email', 'users.phone')
            ->orderBy('users.name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->pivot->role,
            ]);

        $invites = OrganizationInvite::query()
            ->where('organization_id', $org->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'email' => $i->email,
                'role' => $i->role,
                'expires_at' => $i->expires_at?->toDateTimeString(),
                'accepted_at' => $i->accepted_at?->toDateTimeString(),
                'invite_url' => route('invites.show', $i->token),
            ]);

        return Inertia::render('App/Organization/Members', [
            'organization' => $org->only(['id', 'name', 'type']),
            'myRole' => $myRole,
            'members' => $members,
            'invites' => $invites,
        ]);
    }

    public function invite(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        abort_unless($org, 404);
        abort_unless($org->users()->where('users.id', $user->id)->exists(), 403);

        $myRole = $user->orgRole($org->id) ?? 'member';
        abort_unless(in_array($myRole, [\App\Enums\Role::OWNER->value, \App\Enums\Role::ADMIN->value]), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member,viewer'],
            'expires_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $gate = app(\App\Services\UsageService::class)->withinLimits($org);
        if (!$gate['can']['invite_member']) {
            return back()->with('error', 'Member limit reached. Upgrade to invite more members.');
        }

        $email = strtolower($data['email']);

        // Prevent inviting someone who is already a member
        $alreadyMember = $org->users()->where('users.email', $email)->exists();
        if ($alreadyMember) {
            return back()->with('error', 'This email already belongs to a member of the organization.');
        }

        // Prevent multiple active invites (reuse the newest active one)
        $existing = OrganizationInvite::query()
            ->where('organization_id', $org->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->orderByDesc('id')
            ->first();

        if ($existing && (!$existing->expires_at || now()->lessThanOrEqualTo($existing->expires_at))) {
            return back()->with('success', 'Invite already exists. Copy the invite link below.');
        }

        $invite = OrganizationInvite::create([
            'organization_id' => $org->id,
            'email' => $email,
            'role' => $data['role'],
            'token' => Str::random(48),
            'expires_at' => now()->addDays((int)$data['expires_days']),
            'invited_by' => $user->id,
        ]);

        $invite->load('organization');

        try {
            Mail::to($invite->email)->send(new OrganizationInviteMail($invite));
        } catch (\Throwable $e) {
            // The invite record is already committed; a mail transport
            // failure must not turn into a 500. Surface the invite link so
            // the inviter can share it manually.
            Log::warning('Organization invite email failed', [
                'invite_id' => $invite->id,
                'email' => $invite->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('warning', 'Invite created, but the email could not be sent. Copy the invite link and share it manually: ' . route('invites.show', $invite->token));
        }

        return back()->with('success', 'Invite sent to ' . $invite->email . '.');
    }

    public function resend(Request $request, OrganizationInvite $invite)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        abort_unless($org && $invite->organization_id === $org->id, 403);

        $myRole = $user->orgRole($org->id) ?? 'member';
        abort_unless(in_array($myRole, [\App\Enums\Role::OWNER->value, \App\Enums\Role::ADMIN->value]), 403);

        if ($invite->accepted_at) {
            return back()->with('error', 'Invite already accepted.');
        }

        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            return back()->with('error', 'Invite expired. Create a new one.');
        }

        $invite->load('organization');

        try {
            Mail::to($invite->email)->send(new \App\Mail\OrganizationInviteMail($invite));
        } catch (\Throwable $e) {
            Log::warning('Organization invite resend failed', [
                'invite_id' => $invite->id,
                'email' => $invite->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('warning', 'The email could not be sent. Copy the invite link and share it manually: ' . route('invites.show', $invite->token));
        }

        return back()->with('success', 'Invite resent.');
    }


    public function revoke(Request $request, OrganizationInvite $invite)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        abort_unless($org && $invite->organization_id === $org->id, 403);

        $myRole = $user->orgRole($org->id) ?? 'member';
        abort_unless(in_array($myRole, [\App\Enums\Role::OWNER->value, \App\Enums\Role::ADMIN->value]), 403);

        if ($invite->accepted_at) {
            return back()->with('error', 'Cannot revoke an invite that has already been accepted.');
        }

        $invite->delete();

        return back()->with('success', 'Invite revoked.');
    }
    public function update(Request $request, \App\Models\User $member)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        abort_unless($org, 404);

        // Ensure member belongs to this org
        abort_unless($org->users()->where('users.id', $member->id)->exists(), 404);

        Gate::authorize('manageMembers', $org);
        Gate::authorize('changeMemberRole', [$org, $member]);

        $data = $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        if ($data['role'] === 'owner') {
            return back()->with('error', 'Use Transfer Ownership flow to change the organization owner.');
        }

        $org->users()->updateExistingPivot($member->id, ['role' => $data['role']]);

        return back()->with('success', 'Member role updated.');
    }
}
