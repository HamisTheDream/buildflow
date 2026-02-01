<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TodayLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProjectTodayController extends Controller
{
    public function show(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $user = $request->user();
        $orgRole = $user->orgRole($project->organization_id) ?? 'member';

        $today = now()->toDateString();

        // current user's log for today
        $myLog = TodayLog::query()
            ->where('project_id', $project->id)
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('log_date', $today)
            ->with(['attachments.uploader:id,name,email'])
            ->first();

        // show all today's logs to org owner/admin and project roles owner/pm
        $canSeeAll = in_array($orgRole, ['owner', 'admin']);

        if (!$canSeeAll) {
            $projectRole = $project->memberRole($user) ?? 'viewer';
            $canSeeAll = in_array($projectRole, ['owner', 'pm']);
        }

        $todayLogs = [];
        if ($canSeeAll) {
            $todayLogs = TodayLog::query()
                ->where('project_id', $project->id)
                ->where('log_date', $today)
                ->with(['user:id,name,email', 'attachments.uploader:id,name,email'])
                ->orderBy('id')
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'user' => [
                        'id' => $l->user->id,
                        'name' => $l->user->name,
                        'email' => $l->user->email,
                    ],
                    'work_done' => $l->work_done,
                    'blockers' => $l->blockers,
                    'next_steps' => $l->next_steps,
                    'progress_percent' => $l->progress_percent,
                    'weather' => $l->weather,
                    'updated_at' => $l->updated_at->toDateTimeString(),
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
        }

        return Inertia::render('App/Projects/Today', [
            'project' => $project->only(['id','name','status']),
            'myLog' => $myLog ? [
                'id' => $myLog->id,
                'work_done' => $myLog->work_done,
                'blockers' => $myLog->blockers,
                'next_steps' => $myLog->next_steps,
                'progress_percent' => $myLog->progress_percent,
                'weather' => $myLog->weather,
                'log_date' => $myLog->log_date->toDateString(),
                'attachments' => $myLog->attachments->map(fn($a) => [
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
            ] : null,
            'canSeeAll' => $canSeeAll,
            'todayLogs' => $todayLogs,
            'today' => $today,
        ]);
    }

    public function upsert(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $user = $request->user();

        // Only project members OR org owner/admin can create log
        $orgRole = $user->orgRole($project->organization_id) ?? 'member';
        $isProjectMember = $project->members()->where('users.id', $user->id)->exists();

        if (!in_array($orgRole, ['owner', 'admin']) && !$isProjectMember) {
            abort(403);
        }

        $data = $request->validate([
            'work_done' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'next_steps' => ['nullable', 'string'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'weather' => ['nullable', 'string', 'max:100'],
        ]);

        $today = now()->toDateString();

        TodayLog::query()->updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'log_date' => $today,
            ],
            $data
        );

        return back()->with('success', 'Today log saved.');
    }
}
