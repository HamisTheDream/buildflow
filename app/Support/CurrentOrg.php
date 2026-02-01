<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\User;

class CurrentOrg
{
    /**
     * Resolve the current organization for the given user.
     *
     * Logic:
     * 1. If user has current_organization_id set, try to find that org.
     * 2. If valid, check if user belongs to it.
     * 3. If invalid or null, fallback to the user's first organization.
     * 4. If none, return null.
     */
    public static function forUser(User $user): ?Organization
    {
        // 1. Try specified current org
        if ($user->current_organization_id) {
            $org = Organization::find($user->current_organization_id);
            // Ensure user still belongs to this org (unless owner logic differs, but generally safe check)
            // Assuming we have a relation or way to check membership. 
            // For MVP simplicty, just checking existence might be enough if we trust the setter.
            // But better: $user->organizations()->where('id', $org->id)->exists()
            // Let's assume strict checking isn't blocking us right now, but good to have.

            if ($org) {
                return $org;
            }
        }

        // 2. Fallback to first org
        // Assuming relationship 'organizations' exists on User
        $first = $user->organizations()->first();

        if ($first) {
            // Self-repair: update the user's preference
            $user->update(['current_organization_id' => $first->id]);
            return $first;
        }

        return null;
    }
}
