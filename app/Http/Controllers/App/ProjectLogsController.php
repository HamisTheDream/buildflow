<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProjectLogsController extends Controller
{
    private function canWrite(Request $request, Project $project): bool
    {
        $user = $request->user();
        $orgRole = $user->orgRole($project->organization_id) ?? 'member';
        if (in_array($orgRole, ['owner', 'admin'])) return true;

        $projectRole = $project->memberRole($user) ?? 'viewer';
        return in_array($projectRole, ['owner', 'pm', 'clerk']);
    }

    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $types = ['general', 'site_visit', 'progress', 'material', 'labor', 'safety', 'client', 'finance', 'issue'];

        $filters = $request->validate([
            'type' => ['nullable', 'in:' . implode(',', $types)],
            'author' => ['nullable', 'integer'],
            'unit' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $query = ProjectLog::query()
            ->where('project_id', $project->id)
            ->with([
                'user:id,name,email',
                'unit:id,name',
                'attachments.uploader:id,name,email',
            ]);

        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['author'])) $query->where('user_id', (int)$filters['author']);
        if (!empty($filters['unit'])) $query->where('project_unit_id', (int)$filters['unit']);
        if (!empty($filters['from'])) $query->where('log_date', '>=', $filters['from']);
        if (!empty($filters['to'])) $query->where('log_date', '<=', $filters['to']);
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $logs->getCollection()->transform(fn($l) => [
            'id' => $l->id,
            'log_date' => $l->log_date->toDateString(),
            'log_time' => $l->log_time,
            'type' => $l->type,
            'title' => $l->title,
            'body' => $l->body,
            'workforce_count' => $l->workforce_count,
            'weather' => $l->weather,
            'materials_delivered' => $l->materials_delivered,
            'blockers' => $l->blockers,
            'next_day_plan' => $l->next_day_plan,
            'unit' => $l->unit ? $l->unit->only('id', 'name') : null,
            'user' => $l->user ? $l->user->only('id', 'name', 'email') : null,
            'created_at' => $l->created_at->toDateTimeString(),
            'attachments' => $l->attachments->map(fn($a) => [
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

        // Authors list for filter dropdown
        $authors = $project->members()
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        // Include org admins/owners too (they may not be project members)
        $org = $request->user()->currentOrganization;
        if ($org) {
            $orgAdmins = $org->users()
                ->whereIn('organization_user.role', ['owner', 'admin'])
                ->select('users.id', 'users.name')
                ->orderBy('users.name')
                ->get()
                ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

            // merge unique
            $authors = $authors->merge($orgAdmins)->unique('id')->values();
        }

        // Units
        $units = $project->units()->orderBy('name')->select('id', 'name')->get();

        return Inertia::render('App/Projects/Logs', [
            'project' => $project->only(['id', 'name', 'status']),
            'canWrite' => $this->canWrite($request, $project),
            'types' => $types,
            'authors' => $authors,
            'units' => $units,
            'filters' => [
                'type' => $request->query('type', ''),
                'author' => $request->query('author', ''),
                'unit' => $request->query('unit', ''),
                'from' => $request->query('from', ''),
                'to' => $request->query('to', ''),
                'q' => $request->query('q', ''),
            ],
            'logs' => $logs,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($this->canWrite($request, $project), 403);

        $types = ['general', 'site_visit', 'progress', 'material', 'labor', 'safety', 'client', 'finance', 'issue'];

        $data = $request->validate([
            'log_date' => ['required', 'date'],
            'log_time' => ['nullable', 'date_format:H:i'],
            'type' => ['required', 'in:' . implode(',', $types)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'workforce_count' => ['nullable', 'integer', 'min:0'],
            'weather' => ['nullable', 'string', 'max:50'],
            'materials_delivered' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'next_day_plan' => ['nullable', 'string'],
            'project_unit_id' => ['nullable', 'exists:project_units,id'],
        ]);

        if (!empty($data['project_unit_id'])) {
            // Ensure unit belongs to project
            $unit = $project->units()->find($data['project_unit_id']);
            if (!$unit) {
                return back()->with('error', 'Invalid unit.');
            }
        }

        ProjectLog::create([
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return back()->with('success', 'Log added.');
    }

    public function update(Request $request, Project $project, ProjectLog $log)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($log->project_id === $project->id, 404);

        // Only writers can edit; and non-admins can only edit their own logs
        abort_unless($this->canWrite($request, $project), 403);

        $user = $request->user();
        $orgRole = $user->orgRole($project->organization_id) ?? 'member';
        if (!in_array($orgRole, ['owner', 'admin']) && $log->user_id !== $user->id) {
            abort(403);
        }

        $types = ['general', 'site_visit', 'progress', 'material', 'labor', 'safety', 'client', 'finance', 'issue'];

        $data = $request->validate([
            'log_date' => ['required', 'date'],
            'log_time' => ['nullable', 'date_format:H:i'],
            'type' => ['required', 'in:' . implode(',', $types)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'workforce_count' => ['nullable', 'integer', 'min:0'],
            'weather' => ['nullable', 'string', 'max:50'],
            'materials_delivered' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'next_day_plan' => ['nullable', 'string'],
            'project_unit_id' => ['nullable', 'exists:project_units,id'],
        ]);

        if (!empty($data['project_unit_id'])) {
            $unit = $project->units()->find($data['project_unit_id']);
            if (!$unit) {
                return back()->with('error', 'Invalid unit.');
            }
        }

        $log->update($data);

        return back()->with('success', 'Log updated.');
    }

    public function destroy(Request $request, Project $project, ProjectLog $log)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($log->project_id === $project->id, 404);

        abort_unless($this->canWrite($request, $project), 403);

        $user = $request->user();
        $orgRole = $user->orgRole($project->organization_id) ?? 'member';
        if (!in_array($orgRole, ['owner', 'admin']) && $log->user_id !== $user->id) {
            abort(403);
        }

        $log->delete();

        return back()->with('success', 'Log deleted.');
    }
}
