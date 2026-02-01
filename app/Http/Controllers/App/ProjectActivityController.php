<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProjectActivityController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $logs = ActivityLog::query()
            ->where('project_id', $project->id)
            ->with('user:id,name,email')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $logs->getCollection()->transform(fn ($l) => [
            'id' => $l->id,
            'action' => $l->action,
            'entity_type' => $l->entity_type,
            'entity_id' => $l->entity_id,
            'before' => $l->before,
            'after' => $l->after,
            'created_at' => $l->created_at->toDateTimeString(),
            'user' => $l->user ? [
                'id' => $l->user->id,
                'name' => $l->user->name,
                'email' => $l->user->email,
            ] : null,
        ]);

        return Inertia::render('App/Projects/Activity', [
            'project' => $project->only(['id','name','status']),
            'logs' => $logs,
        ]);
    }
}
