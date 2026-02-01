<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use App\Services\ActivityLogger;

class ProjectTasksController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $filters = $request->validate([
            'status' => ['nullable', 'in:todo,doing,done,blocked'],
            'assignee' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $query = ProjectTask::query()
            ->where('project_id', $project->id)
            ->with([
                'assignee:id,name,email',
                'creator:id,name,email',
                'attachments.uploader:id,name,email',
            ]);

        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['assignee'])) $query->where('assigned_to', (int)$filters['assignee']);
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $tasks = $query
            ->orderByRaw("CASE status WHEN 'todo' THEN 1 WHEN 'doing' THEN 2 WHEN 'blocked' THEN 3 WHEN 'done' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $tasks->getCollection()->transform(fn ($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status,
            'priority' => $t->priority,
            'due_date' => $t->due_date?->toDateString(),
            'assigned_to' => $t->assigned_to,
            'project_id' => $t->project_id,
            'assignee' => $t->assignee ? $t->assignee->only('id','name','email') : null,
            'creator' => $t->creator ? $t->creator->only('id','name','email') : null,
            'attachments' => $t->attachments->map(fn($a) => [
                'id' => $a->id,
                'url' => $a->url(),
                'is_image' => $a->isImage(),
                'original_name' => $a->original_name,
                'mime' => $a->mime,
                'size' => $a->size,
                'caption' => $a->caption,
                'created_at' => $a->created_at->toDateTimeString(),
                'uploader' => $a->uploader ? [
                    'id' => $a->uploader->id,
                    'name' => $a->uploader->name,
                    'email' => $a->uploader->email,
                ] : null,
                'can_delete' => (int)$a->uploaded_by === (int)$request->user()->id,
            ])->values(),
        ]);

        $assignees = $project->members()
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('App/Projects/Tasks', [
            'project' => $project->only(['id','name','status']),
            'canManage' => $request->user()->can('create', [ProjectTask::class, $project]),
            'assignees' => $assignees,
            'filters' => [
                'status' => $request->query('status', ''),
                'assignee' => $request->query('assignee', ''),
                'q' => $request->query('q', ''),
            ],
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectTask::class, $project]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done,blocked'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer'],
        ]);

        // assigned_to must be a project member if provided
        if (!empty($data['assigned_to'])) {
            $isMember = $project->members()->where('users.id', (int)$data['assigned_to'])->exists();
            abort_unless($isMember, 422);
        }

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'created_by' => $request->user()->id,
            ...$data,
        ]);

        if (!empty($task->assigned_to)) {
            $assignee = \App\Models\User::find($task->assigned_to);
             if ($assignee) {
                $assignee->notify(new \App\Notifications\TaskAssigned($project, $task));
            }
        }

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'created', 'tasks', null, $task);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, Project $project, ProjectTask $task, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($task->project_id === $project->id, 404);

        $isManager = $request->user()->can('update', [$task, $project]);
        $canStatus = $request->user()->can('updateStatus', [$task, $project]);

        abort_unless($isManager || $canStatus, 403);

        if (!$isManager) {
            $data = $request->validate([
                'status' => ['required', 'in:todo,doing,done,blocked'],
            ]);

            $oldStatus = $task->status;
            $task->update($data);

            if ($oldStatus !== $task->status) {
                $activity->log($request, $project->organization_id, $project->id, $request->user()->id, 'status_changed', 'tasks', $task->id, ['status' => $oldStatus], ['status' => $task->status]);
            }

            return back()->with('success', 'Task status updated.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done,blocked'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer'],
        ]);

        if (!empty($data['assigned_to'])) {
            $isMember = $project->members()->where('users.id', (int)$data['assigned_to'])->exists();
            abort_unless($isMember, 422);
        }

        $before = $task->replicate();
        $task->update($data);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'updated', 'tasks', $before, $task);

        if ($task->assigned_to && (int)$task->assigned_to !== (int)$before->assigned_to) {
            $assignee = \App\Models\User::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(new \App\Notifications\TaskAssigned($project, $task));
            }
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Request $request, Project $project, ProjectTask $task, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($task->project_id === $project->id, 404);

        Gate::authorize('delete', [$task, $project]);

        $before = $task->replicate();
        $task->delete();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'deleted', 'tasks', $before, null);

        return back()->with('success', 'Task deleted.');
    }
}
