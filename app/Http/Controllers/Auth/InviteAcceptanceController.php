<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OrganizationInvite;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InviteAcceptanceController extends Controller
{
    /**
     * Guest view: show invite accept page (name + password)
     */
    public function show(Request $request, string $token)
    {
        $invite = $this->getValidInviteOrAbort($token);

        $email = strtolower($invite->email);

        // If user is already logged in: try immediate accept
        if ($request->user()) {
            // email mismatch -> clear any pending token and show forbidden
            if (strtolower($request->user()->email) !== $email) {
                $request->session()->forget('pending_invite_token');
                abort(403, 'This invite is for a different email address.');
            }

            // Auto-accept immediately for logged-in user
            $this->acceptInviteForUser($invite, $request->user());

            return redirect()->route('app.dashboard')
                ->with('success', "You joined {$invite->organization->name}.");
        }

        // Guest: check if this email already has an account
        $existingUser = \App\Models\User::query()
            ->where('email', $email)
            ->exists();

        // If user exists, store token so after login we can auto-accept
        if ($existingUser) {
            $request->session()->put('pending_invite_token', $token);
        } else {
            $request->session()->forget('pending_invite_token');
        }

        return Inertia::render('Auth/AcceptInvite', [
            'token' => $token,
            'invite' => [
                'organization_name' => $invite->organization->name,
                'organization_type' => $invite->organization->type,
                'email' => $invite->email,
                'role' => $invite->role,
                'expires_at' => $invite->expires_at?->toDateTimeString(),
            ],
            'existingUser' => $existingUser,
        ]);
    }


    /**
     * Guest submit: create account + attach to org + login
     */
    public function store(Request $request, string $token)
    {
        $invite = $this->getValidInviteOrAbort($token);

        $email = strtolower($invite->email);

        // If user already exists, don't override password.
        // Force them to login and accept via auth route.
        $existingUser = User::query()->where('email', $email)->first();
        if ($existingUser) {
            return redirect()->route('login')->with('error', 'An account with this email already exists. Please login to accept the invite.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_invited_only' => true,
        ]);

        // Attach membership
        $org = $invite->organization;

        $org->users()->syncWithoutDetaching([
            $user->id => ['role' => $invite->role], // member|admin
        ]);

        // Mark invite accepted
        $invite->accepted_at = now();
        $invite->accepted_by = $user->id;
        $invite->save();

        // Set current org
        $user->current_organization_id = $org->id;
        $user->save();

        event(new Registered($user));
        Auth::login($user);

        \App\Http\Controllers\App\DashboardController::clearCache($org->id);

        return redirect()->route('app.dashboard')
            ->with('success', "Welcome to {$org->name}.");
    }

    /**
     * Auth flow: accept invite for an already-registered user
     */
    public function acceptWhileLoggedIn(Request $request, string $token)
    {
        $invite = $this->getValidInviteOrAbort($token);

        $user = $request->user();
        abort_unless($user, 403);

        if (strtolower($user->email) !== strtolower($invite->email)) {
            abort(403, 'This invite is for a different email address.');
        }

        $this->acceptInviteForUser($invite, $user);

        return redirect()->route('app.dashboard')
            ->with('success', "You joined {$invite->organization->name}.");
    }


    private function getValidInviteOrAbort(string $token): OrganizationInvite
    {
        $invite = OrganizationInvite::query()
            ->where('token', $token)
            ->with('organization')
            ->firstOrFail();

        if ($invite->accepted_at) {
            abort(410, 'This invite has already been used.');
        }

        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            abort(410, 'This invite has expired.');
        }

        return $invite;
    }

    private function acceptInviteForUser(\App\Models\OrganizationInvite $invite, \App\Models\User $user): void
    {
        $org = $invite->organization;

        $org->users()->syncWithoutDetaching([
            $user->id => ['role' => $invite->role],
        ]);

        $invite->accepted_at = now();
        $invite->accepted_by = $user->id;
        $invite->save();

        $user->current_organization_id = $org->id;
        $user->save();

        \App\Http\Controllers\App\DashboardController::clearCache($org->id);
    }
}
