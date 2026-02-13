<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;

class Access
{
    /**
     * Check if user can view the project (Member or Org Admin).
     */
    public static function canViewProject(User $user, Project $project): bool
    {
        return $project->members()->where('users.id', $user->id)->exists()
            || $user->isOrgAdminOrAbove($project->organization_id);
    }

    /**
     * Check if user is an Organization Manager (Owner or Admin).
     */
    public static function isOrgManager(User $user, int $organizationId): bool
    {
        return $user->isOrgAdminOrAbove($organizationId);
    }

    /**
     * Check if user has one of the specified roles in the project.
     */
    public static function isProjectRole(User $user, Project $project, array $roles): bool
    {
        $userRole = $user->projectRole($project->id);

        if (!$userRole) {
            return false;
        }

        return in_array($userRole, $roles, true);
    }
}
