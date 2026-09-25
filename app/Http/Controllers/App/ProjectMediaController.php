<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use App\Services\ActivityLogger;

class ProjectMediaController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $media = ProjectMedia::query()
            ->where('project_id', $project->id)
            ->with('uploader:id,name,email')
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString();

        // Map to safe payload
        $media->getCollection()->transform(function ($m) {
            /** @var \App\Models\ProjectMedia $m */
            return [
                'id' => $m->id,
                'url' => $m->url(),
                'is_image' => $m->isImage(),
                'original_name' => $m->original_name,
                'mime' => $m->mime,
                'size' => $m->size,
                'caption' => $m->caption,
                'uploaded_by' => $m->uploaded_by,
                'uploader' => $m->uploader ? [
                    'id' => $m->uploader->id,
                    'name' => $m->uploader->name,
                    'email' => $m->uploader->email,
                ] : null,
                'created_at' => $m->created_at->toDateTimeString(),
            ];
        });

        return Inertia::render('App/Projects/Media', [
            'project' => $project->only(['id', 'name', 'status']),
            'canManage' => $request->user()->can('create', [ProjectMedia::class, $project]),
            'media' => $media,
        ]);
    }

    public function store(\App\Http\Requests\StoreMediaRequest $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectMedia::class, $project]);

        $data = $request->validated();

        foreach ($request->file('files') as $file) {
            $disk = config('filesystems.uploads_disk');
            $path = $file->store("projects/{$project->id}/media", $disk);

            $media = ProjectMedia::create([
                'project_id' => $project->id,
                'uploaded_by' => $request->user()->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'caption' => $data['caption'] ?? null,
            ]);

            $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'uploaded', 'media', null, $media);
        }

        return back()->with('success', 'Files uploaded.');
    }

    public function update(\App\Http\Requests\StoreMediaRequest $request, Project $project, ProjectMedia $media, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($media->project_id === $project->id, 404);

        Gate::authorize('update', [$media, $project]);

        $data = $request->validated();

        $before = $media->replicate();
        $media->update($data);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'updated', 'media', $before, $media);

        return back()->with('success', 'Caption updated.');
    }

    public function destroy(Request $request, Project $project, ProjectMedia $media, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($media->project_id === $project->id, 404);

        Gate::authorize('delete', [$media, $project]);

        Storage::disk($media->disk)->delete($media->path);

        $before = $media->replicate();
        // force: the R2 object is already gone; SoftDeletes must not leave a file-less row
        $media->forceDelete();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'deleted', 'media', $before, null);

        return back()->with('success', 'File deleted.');
    }
}
