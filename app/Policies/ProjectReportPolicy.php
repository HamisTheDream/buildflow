<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use App\Support\Access;

class ProjectReportPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return Access::canViewProject($user, $project);
    }

    public function generate(User $user, Project $project): bool
    {
        // Anyone who can view can generate (useful for clerks)
        return Access::canViewProject($user, $project);
    }

    public function email(User $user, Project $project): bool
    {
        // Anyone who can view can email (or restrict to managers; your call)
        return Access::canViewProject($user, $project);
    }

    public function share(User $user, Project $project): bool
    {
        // Sharing is a stronger action -> owner/pm + org managers
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm']);
    }

    public function delete(User $user, ProjectReport $report, Project $project): bool
    {
        if ($report->project_id !== $project->id) return false;

        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm']);
    }
}
