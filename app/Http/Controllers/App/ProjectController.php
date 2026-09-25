<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization;

        if (!$org) {
            return redirect()->route('app.dashboard')->with('error', 'No organization selected.');
        }

        // Org owner/admin sees all org projects; others see only assigned projects
        $orgRole = $user->orgRole($org->id) ?? \App\Enums\Role::MEMBER->value;

        $query = Project::query()->where('organization_id', $org->id);

        if (!in_array($orgRole, [\App\Enums\Role::OWNER->value, \App\Enums\Role::ADMIN->value])) {
            $query->whereHas('members', fn($q) => $q->where('users.id', $user->id));
        }

        $projects = $query
            ->orderByDesc('id')
            ->select(['id', 'name', 'status', 'location', 'start_date', 'end_date', 'client_name'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('App/Projects/Index', [
            'organization' => $org->only(['id', 'name', 'type']),
            'orgRole' => $orgRole,
            'projects' => $projects,
            'canCreate' => $request->user()->can('create', Project::class),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Project::class);

        $org = $request->user()->currentOrganization;

        return Inertia::render('App/Projects/Create', [
            'organization' => $org->only(['id', 'name', 'type']),
        ]);
    }

    public function store(\App\Http\Requests\StoreProjectRequest $request)
    {
        // TEMPORARY DIAGNOSTIC (2026-09-25): the plan-limit path still 500s in
        // production despite the fail-closed gate. Surface the real exception
        // in the flash message so the browser probe can report it. REMOVE after
        // root cause is identified.
        try {
            return $this->doStore($request);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'DEBUG-STORE ' . get_class($e) . ': ' . $e->getMessage());
        }
    }

    private function doStore(\App\Http\Requests\StoreProjectRequest $request)
    {
        Gate::authorize('create', Project::class);

        $org = $request->user()->currentOrganization;

        // Fail closed: if the usage/plan gate cannot be evaluated for any
        // reason, treat the project as over-limit rather than 500ing. The
        // exception is still reported so the root cause can be fixed.
        $canCreateProject = false;
        try {
            $gate = app(\App\Services\UsageService::class)->withinLimits($org);
            $canCreateProject = (bool) ($gate['can']['create_project'] ?? false);
        } catch (\Throwable $e) {
            report($e);
        }

        if (!$canCreateProject) {
            return back()->with('error', 'Your plan has reached the project limit. Upgrade to create more projects.');
        }

        $data = $request->validated();

        $project = Project::create([
            ...$data,
            'organization_id' => $org->id,
        ]);

        // Auto-add creator as project owner
        $project->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => 'owner'],
        ]);

        \App\Http\Controllers\App\DashboardController::clearCache($org->id);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Request $request, Project $project, \App\Services\ProjectStatisticsService $statsService)
    {
        Gate::authorize('view', $project);

        $metrics = $statsService->getProjectMetrics($project);

        return Inertia::render('App/Projects/Show', [
            'project' => $project->only(['id', 'name', 'status', 'location', 'start_date', 'end_date', 'client_name', 'client_phone', 'client_email', 'description', 'budget']),
            'metrics' => $metrics,
        ]);
    }
}
