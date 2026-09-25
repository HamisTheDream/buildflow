<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProjectAttachmentsController extends Controller
{
    public function store(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [Attachment::class, $project]);

        $org = $project->organization;
        $gate = app(\App\Services\UsageService::class)->withinLimits($org);

        if (!$gate['can']['upload']) {
            return back()->with('error', 'Storage limit reached. Upgrade to upload more files.');
        }

        $data = $request->validate([
            'attachable_type' => ['required', 'string'],
            'attachable_id' => ['required', 'integer'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:10240', new \App\Rules\SafeFile], // 10MB
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $typeMap = [
            'issue' => \App\Models\ProjectIssue::class,
            'task' => \App\Models\ProjectTask::class,
            'log' => \App\Models\ProjectLog::class,
            'today_log' => \App\Models\TodayLog::class,
            'cost' => \App\Models\ProjectCost::class,
        ];

        abort_unless(isset($typeMap[$data['attachable_type']]), 422);

        $attachableClass = $typeMap[$data['attachable_type']];
        $attachable = $attachableClass::query()->findOrFail((int)$data['attachable_id']);

        // Ensure attachable belongs to this project
        abort_unless((int)$attachable->project_id === (int)$project->id, 403);

        foreach ($request->file('files') as $file) {
            $disk = config('filesystems.uploads_disk');
            $path = $file->store("projects/{$project->id}/attachments", $disk);

            $att = Attachment::create([
                'organization_id' => (int)$project->organization_id,
                'project_id' => (int)$project->id,
                'uploaded_by' => (int)$request->user()->id,
                'attachable_type' => $attachableClass,
                'attachable_id' => (int)$attachable->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'caption' => $data['caption'] ?? null,
            ]);

            $activity->logModel(
                $request,
                (int)$project->organization_id,
                (int)$project->id,
                (int)$request->user()->id,
                'created',
                'attachments',
                null,
                $att
            );
        }

        return back()->with('success', 'Attachment(s) added.');
    }

    public function destroy(Request $request, Project $project, Attachment $attachment, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('delete', [$attachment, $project]);

        $before = $attachment->replicate();

        Storage::disk($attachment->disk)->delete($attachment->path);
        // force: the R2 object is already gone; SoftDeletes must not leave a file-less row
        $attachment->forceDelete();

        $activity->logModel(
            $request,
            (int)$project->organization_id,
            (int)$project->id,
            (int)$request->user()->id,
            'deleted',
            'attachments',
            $before,
            null
        );

        return back()->with('success', 'Attachment removed.');
    }
}
