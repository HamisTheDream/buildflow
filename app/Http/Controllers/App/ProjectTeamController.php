<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Gate;

use App\Services\ActivityLogger;

class ProjectTeamController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);
        
        $org = $request->user()->currentOrganization;

        // Org members list (for assignment)
        $orgMembers = $org->users()
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'org_role' => $u->pivot->role,
            ]);

        // Current project members
        $projectMembers = $project->members()
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'project_role' => $u->pivot->role,
            ]);

        // For UI convenience: which org members already in project
        $memberIds = $project->members()->pluck('users.id')->all();

        return Inertia::render('App/Projects/Team', [
            'project' => $project->only(['id', 'name']),
            'canManage' => $request->user()->can('manageTeam', $project),
            'orgMembers' => $orgMembers,
            'projectMembers' => $projectMembers,
            'projectMemberIds' => $memberIds,
            'roles' => [
                ['value' => 'owner', 'label' => 'Owner'],
                ['value' => 'pm', 'label' => 'Project Manager'],
                ['value' => 'clerk', 'label' => 'Clerk'],
                ['value' => 'accountant', 'label' => 'Accountant'],
                ['value' => 'viewer', 'label' => 'Viewer'],
            ],
        ]);
    }

    public function store(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('manageTeam', $project);

        $org = $request->user()->currentOrganization;

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'in:owner,pm,clerk,accountant,viewer'],
        ]);

        $userToAdd = User::query()->findOrFail($data['user_id']);

        // Must belong to same org
        $isOrgMember = $org->users()->where('users.id', $userToAdd->id)->exists();
        abort_unless($isOrgMember, 422);

        // Attach (or update role if already attached)
        $project->members()->syncWithoutDetaching([
            $userToAdd->id => ['role' => $data['role']],
        ]);

        $activity->log(
            $request, 
            $project->organization_id, 
            $project->id, 
            $request->user()->id, 
            'member_added', 
            'team', 
            $userToAdd->id, 
            null, 
            ['role' => $data['role']]
        );

        return back()->with('success', 'Member added to project.');
    }

    public function update(Request $request, Project $project, User $user, ActivityLogger $activity)
    {
        // Must be able to manage team AND be allowed to change this specific user's role
        Gate::authorize('manageTeam', $project);
        Gate::authorize('changeMemberRole', [$project, $user]);

        $data = $request->validate([
            'role' => ['required', 'in:owner,pm,clerk,accountant,viewer'],
        ]);

        // Block promoting someone to owner here (requires controlled transfer flow)
        if ($data['role'] === 'owner') {
            return back()->with('error', 'Use the Transfer Ownership flow to change the project owner.');
        }

        // Ensure target is actually a member of the project
        $isMember = $project->members()->where('users.id', $user->id)->exists();
        if (!$isMember) {
            return back()->with('error', 'User is not a member of this project.');
        }

        $currentRole = $project->memberRole($user);

        $project->members()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        $activity->log(
            $request, 
            $project->organization_id, 
            $project->id, 
            $request->user()->id, 
            'role_updated', 
            'team', 
            $user->id, 
            ['role' => $currentRole], 
            ['role' => $data['role']]
        );

        return back()->with('success', 'Member role updated.');
    }

    public function destroy(Request $request, Project $project, User $user, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('manageTeam', $project);

        // Prevent removing the last project owner
        $isOwner = $project->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if ($isOwner) {
            $ownersCount = $project->members()->wherePivot('role', 'owner')->count();
            if ($ownersCount <= 1) {
                return back()->with('error', 'You cannot remove the last project owner.');
            }
        }

        $currentRole = $project->memberRole($user);

        $project->members()->detach($user->id);

        $activity->log(
            $request, 
            $project->organization_id, 
            $project->id, 
            $request->user()->id, 
            'member_removed', 
            'team', 
            $user->id, 
            ['role' => $currentRole], 
            null
        );

        return back()->with('success', 'Member removed from project.');
    }
}
