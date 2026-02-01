<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\User;
use App\Support\Access;

class ProjectIssuePolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return Access::canViewProject($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm', 'clerk']);
    }

    public function update(User $user, ProjectIssue $issue, Project $project): bool
    {
        if ($issue->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }

    public function updateStatus(User $user, ProjectIssue $issue, Project $project): bool
    {
        if ($issue->project_id !== $project->id) return false;

        if ($this->create($user, $project)) return true;
        return (int)$issue->assigned_to === (int)$user->id;
    }

    public function delete(User $user, ProjectIssue $issue, Project $project): bool
    {
        if ($issue->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }
}
