<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProjectUnitsController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $units = $project->units()
            ->withCount(['tasks', 'issues', 'logs'])
            ->orderBy('name')
            ->get();

        return Inertia::render('App/Projects/Units', [
            'project' => $project->only(['id', 'name', 'status']),
            'units' => $units,
            'canManage' => $request->user()->can('editContent', $project),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,completed,sold'],
            'description' => ['nullable', 'string'],
        ]);

        $project->units()->create($data);

        return back()->with('success', 'Unit created.');
    }

    public function update(Request $request, Project $project, ProjectUnit $unit)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($unit->project_id === $project->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,completed,sold'],
            'description' => ['nullable', 'string'],
        ]);

        $unit->update($data);

        return back()->with('success', 'Unit updated.');
    }

    public function destroy(Request $request, Project $project, ProjectUnit $unit)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($unit->project_id === $project->id, 404);

        if ($unit->logs()->exists() || $unit->tasks()->exists() || $unit->issues()->exists() || $unit->costs()->exists()) {
            return back()->with('error', 'Cannot delete unit with associated records. Archive it instead.');
        }

        $unit->delete();

        return back()->with('success', 'Unit deleted.');
    }
}
