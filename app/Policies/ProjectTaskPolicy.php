<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Support\Access;

class ProjectTaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return Access::canViewProject($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        // Same as project manage operations
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm', 'clerk']);
    }

    public function update(User $user, ProjectTask $task, Project $project): bool
    {
        if ($task->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }

    // Assignee can update status even if not manager
    public function updateStatus(User $user, ProjectTask $task, Project $project): bool
    {
        if ($task->project_id !== $project->id) return false;

        if ($this->create($user, $project)) return true;
        return (int)$task->assigned_to === (int)$user->id;
    }

    public function delete(User $user, ProjectTask $task, Project $project): bool
    {
        if ($task->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }
}
