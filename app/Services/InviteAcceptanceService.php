<?php

namespace App\Services;

use App\Models\OrganizationInvite;
use App\Models\User;

class InviteAcceptanceService
{
    /**
     * Attempt to accept a pending invite for the given user.
     * Returns the organization if accepted, null otherwise.
     */
    public function acceptPendingInvite(User $user, ?string $token): ?\App\Models\Organization
    {
        if (!$token) {
            return null;
        }

        $invite = OrganizationInvite::query()
            ->where('token', $token)
            ->with('organization')
            ->first();

        if (!$invite || $invite->accepted_at) {
            return null;
        }

        $emailMatches = strtolower($invite->email) === strtolower($user->email);
        $notExpired = !$invite->expires_at || now()->lessThanOrEqualTo($invite->expires_at);

        if (!$emailMatches || !$notExpired) {
            return null;
        }

        $org = $invite->organization;

        // Attach membership
        $org->users()->syncWithoutDetaching([
            $user->id => ['role' => $invite->role],
        ]);

        // Mark invite as accepted
        $invite->accepted_at = now();
        $invite->accepted_by = $user->id;
        $invite->save();

        // Update user's current org
        $user->current_organization_id = $org->id;
        $user->save();

        return $org;
    }
}
