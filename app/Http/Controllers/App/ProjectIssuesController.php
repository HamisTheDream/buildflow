<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use App\Services\ActivityLogger;
use App\Notifications\IssueReportedNotification;

class ProjectIssuesController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $filters = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,blocked,resolved,closed'],
            'severity' => ['nullable', 'in:low,medium,high,critical'],
            'assignee' => ['nullable', 'integer'],
            'unit' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $query = ProjectIssue::query()
            ->where('project_id', $project->id)
            ->with([
                'assignee:id,name,email',
                'creator:id,name,email',
                'resolver:id,name,email',
                'unit:id,name',
                'attachments.uploader:id,name,email',
            ]);

        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['severity'])) $query->where('severity', $filters['severity']);
        if (!empty($filters['assignee'])) $query->where('assigned_to', (int)$filters['assignee']);
        if (!empty($filters['unit'])) $query->where('project_unit_id', (int)$filters['unit']);
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $issues = $query
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'blocked' THEN 3 WHEN 'resolved' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $issues->getCollection()->transform(fn($i) => [
            'id' => $i->id,
            'title' => $i->title,
            'description' => $i->description,
            'status' => $i->status,
            'severity' => $i->severity,
            'category' => $i->category,
            'due_date' => $i->due_date?->toDateString(),
            'assigned_to' => $i->assigned_to,
            'project_id' => $i->project_id,
            'unit' => $i->unit ? $i->unit->only('id', 'name') : null,
            'created_at' => $i->created_at->toDateTimeString(),
            'assignee' => $i->assignee ? $i->assignee->only('id', 'name', 'email') : null,
            'creator' => $i->creator ? $i->creator->only('id', 'name', 'email') : null,
            'resolver' => $i->resolver ? $i->resolver->only('id', 'name', 'email') : null,
            'attachments' => $i->attachments->map(fn($a) => [
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
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        $units = $project->units()->orderBy('name')->select('id', 'name')->get();

        return Inertia::render('App/Projects/Issues', [
            'project' => $project->only(['id', 'name', 'status']),
            'canManage' => $request->user()->can('create', [ProjectIssue::class, $project]),
            'assignees' => $assignees,
            'units' => $units,
            'filters' => [
                'status' => $request->query('status', ''),
                'severity' => $request->query('severity', ''),
                'assignee' => $request->query('assignee', ''),
                'unit' => $request->query('unit', ''),
                'q' => $request->query('q', ''),
            ],
            'issues' => $issues,
        ]);
    }

    public function store(\App\Http\Requests\StoreIssueRequest $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectIssue::class, $project]);

        $data = $request->validated();

        $resolved_at = null;
        $resolved_by = null;
        if (in_array($data['status'], ['resolved', 'closed'])) {
            $resolved_at = now();
            $resolved_by = $request->user()->id;
        }

        $issue = ProjectIssue::create([
            'project_id' => $project->id,
            'created_by' => $request->user()->id,
            'resolved_at' => $resolved_at,
            'resolved_by' => $resolved_by,
            ...$data,
        ]);

        if (!empty($issue->assigned_to) && (int)$issue->assigned_to !== (int)$request->user()->id) {
            $assignee = \App\Models\User::find($issue->assigned_to);
            if ($assignee) {
                \App\Models\Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'issue_reported',
                    'title' => "Issue reported: {$issue->title}",
                    'body' => "{$request->user()->name} reported an issue on {$project->name}",
                    'data' => ['project_id' => $project->id, 'issue_id' => $issue->id, 'severity' => $issue->severity],
                ]);
                $assignee->notify(new IssueReportedNotification($issue, $project, $request->user()->name));
            }
        }

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'created', 'issues', null, $issue);

        return back()->with('success', 'Issue created.');
    }

    public function update(\App\Http\Requests\StoreIssueRequest $request, Project $project, ProjectIssue $issue, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($issue->project_id === $project->id, 404);

        $isManager = $request->user()->can('update', [$issue, $project]);
        $canStatus = $request->user()->can('updateStatus', [$issue, $project]);

        abort_unless($isManager || $canStatus, 403);

        // Assignee-only: can only update status
        if (!$isManager) {
            $data = $request->safe()->only(['status']);

            if (in_array($data['status'], ['resolved', 'closed']) && !$issue->resolved_at) {
                $issue->resolved_at = now();
                $issue->resolved_by = $request->user()->id;
            }

            // If re-opened
            if (in_array($data['status'], ['open', 'in_progress', 'blocked'])) {
                $issue->resolved_at = null;
                $issue->resolved_by = null;
            }

            $oldStatus = $issue->status;
            $issue->status = $data['status'];
            $issue->save();

            if ($oldStatus !== $issue->status) {
                $activity->log($request, $project->organization_id, $project->id, $request->user()->id, 'status_changed', 'issues', $issue->id, ['status' => $oldStatus], ['status' => $issue->status]);
            }

            return back()->with('success', 'Issue status updated.');
        }

        $data = $request->validated();

        // resolved fields management
        if (in_array($data['status'], ['resolved', 'closed'])) {
            $issue->resolved_at = $issue->resolved_at ?: now();
            $issue->resolved_by = $issue->resolved_by ?: $request->user()->id;
        } else {
            $issue->resolved_at = null;
            $issue->resolved_by = null;
        }

        $before = $issue->replicate();
        $issue->fill($data);
        $issue->save();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'updated', 'issues', $before, $issue);

        if ($issue->assigned_to && (int)$issue->assigned_to !== (int)$before->assigned_to) {
            $assignee = \App\Models\User::find($issue->assigned_to);
            if ($assignee) {
                \App\Models\Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'issue_assigned',
                    'title' => "Issue reassigned: {$issue->title}",
                    'body' => "{$request->user()->name} assigned you an issue on {$project->name}",
                    'data' => ['project_id' => $project->id, 'issue_id' => $issue->id],
                ]);
                $assignee->notify(new IssueReportedNotification($issue, $project, $request->user()->name));
            }
        }

        return back()->with('success', 'Issue updated.');
    }

    public function destroy(Request $request, Project $project, ProjectIssue $issue, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($issue->project_id === $project->id, 404);

        Gate::authorize('delete', [$issue, $project]);

        $before = $issue->replicate();
        $issue->delete();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'deleted', 'issues', $before, null);

        return back()->with('success', 'Issue deleted.');
    }
}
