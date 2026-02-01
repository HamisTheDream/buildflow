<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can manage organization members.
     */
    public function manageMembers(User $user, Organization $org): bool
    {
        return $user->isOrgAdmin($org->id);
    }

    /**
     * Determine whether the user can change another member's role.
     */
    public function changeMemberRole(User $actor, Organization $org, User $target): bool
    {
        if (!$actor->isOrgAdmin($org->id)) return false;

        // No self role changes
        if ($actor->id === $target->id) return false;

        // Nobody can change owner role (must use transfer ownership)
        if ($target->isOrgOwner($org->id)) return false;

        return true;
    }
}
