<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\User;
use App\Support\Access;

class ProjectMediaPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return Access::canViewProject($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        // Owner, PM, Clerk, or Org Admin can upload media
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm', 'clerk']);
    }

    public function delete(User $user, ProjectMedia $media, Project $project): bool
    {
        if ($media->project_id !== $project->id) return false;

        // Org Managers can always delete
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;

        // Uploader can delete their own media
        if ((int)$media->uploaded_by === (int)$user->id) return true;

        // Project Owner/PM can delete any media
        return Access::isProjectRole($user, $project, ['owner', 'pm']);
    }
}
