<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Can view if they are a member OR org admin
        // Note: projectRole() queries the DB, it's safer to check existence first if perf matters,
        // but for now we rely on the helper or direct relation check.
        // Let's stick to the definition: Member of access OR Org Admin.
        
        return $project->members()->where('users.id', $user->id)->exists()
            || $user->isOrgAdmin($project->organization_id);
    }

    /**
     * Determine whether the user can edit content in the project.
     * (Tasks, Issues, Costs, Logs, Media, Reports)
     */
    public function editContent(User $user, Project $project): bool
    {
        // Viewer is read-only ALWAYS
        $role = $user->projectRole($project->id);
        
        if ($role === 'viewer') return false;

        // Allow: owner/pm/accountant/clerk OR org admin
        return in_array($role, ['owner', 'pm', 'accountant', 'clerk'], true)
            || $user->isOrgAdmin($project->organization_id);
    }

    /**
     * Determine whether the user can manage the project team.
     */
    public function manageTeam(User $user, Project $project): bool
    {
        // Only project owner OR org admin
        return $user->isProjectOwner($project->id)
            || $user->isOrgAdmin($project->organization_id);
    }

    /**
     * Determine whether the user can change another member's role.
     */
    public function changeMemberRole(User $actor, Project $project, User $target): bool
    {
        if (!$this->manageTeam($actor, $project)) return false;

        // No self role changes (prevents escalation / lowering owner)
        if ($actor->id === $target->id) return false;

        // Nobody can change org owner's project membership role
        // (org owner must remain protected)
        if ($target->isOrgOwner($project->organization_id)) return false;

        // Nobody can change the project owner role here (use dedicated transfer flow later)
        if ($target->isProjectOwner($project->id)) return false;

        return true;
    }

    /**
     * Determine whether the user can generate reports.
     * (Usually same as edit content, or specific roles. Let's use editContent logic for now)
     */
    public function generate(User $user, Project $project): bool
    {
        return $this->editContent($user, $project);
    }

     /**
     * Determine whether the user can email reports.
     */
    public function email(User $user, Project $project): bool
    {
        return $this->editContent($user, $project);
    }
    
     /**
     * Determine whether the user can delete reports.
     */
    public function delete(User $user, Project $project): bool
    {
        // Maybe strictly owners? or generally editors? 
        // Let's stick to editContent for simplicity, or restrict to PM/Owner.
        // For 'delete' of items, usually same as editContent in this system unless specified.
        return $this->editContent($user, $project);
    }

}
