<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\User;
use App\Support\Access;

class ProjectCostPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return Access::canViewProject($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        if (Access::isOrgManager($user, (int)$project->organization_id)) return true;
        return Access::isProjectRole($user, $project, ['owner', 'pm', 'accountant']);
    }

    public function update(User $user, ProjectCost $cost, Project $project): bool
    {
        if ($cost->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }

    public function delete(User $user, ProjectCost $cost, Project $project): bool
    {
        if ($cost->project_id !== $project->id) return false;
        return $this->create($user, $project);
    }
}
