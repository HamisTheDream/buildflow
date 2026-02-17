<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\User;

class CurrentOrg
{
    /**
     * Request-level cache to avoid re-querying on repeated calls.
     */
    private static ?Organization $cached = null;
    private static ?int $cachedForUserId = null;

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
        // Return cached result if same user within same request
        if (self::$cachedForUserId === $user->id && self::$cached !== null) {
            return self::$cached;
        }

        self::$cachedForUserId = $user->id;

        // 1. Try specified current org (with membership verification)
        if ($user->current_organization_id) {
            $org = Organization::find($user->current_organization_id);

            // Verify user actually belongs to this org
            if ($org && $user->organizations()->where('organizations.id', $org->id)->exists()) {
                self::$cached = $org;
                return $org;
            }
        }

        // 2. Fallback to first org
        $first = $user->organizations()->first();

        if ($first) {
            // Self-repair: update the user's preference
            $user->update(['current_organization_id' => $first->id]);
            self::$cached = $first;
            return $first;
        }

        self::$cached = null;
        return null;
    }

    /**
     * Clear cache (useful for testing).
     */
    public static function flush(): void
    {
        self::$cached = null;
        self::$cachedForUserId = null;
    }
}
