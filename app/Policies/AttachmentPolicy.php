<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use App\Support\Access;

class AttachmentPolicy
{
    public function create(User $user, Project $project): bool
    {
        // Allow attachments by project operators (owner/pm/clerk) and org managers
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner','pm','clerk']);
    }

    public function delete(User $user, Attachment $attachment, Project $project): bool
    {
        if ($attachment->project_id !== $project->id) return false;

        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        if (Access::isProjectRole($user, $project, ['owner','pm'])) return true;

        // uploader can remove their own
        return (int)$attachment->uploaded_by === (int)$user->id;
    }
}
