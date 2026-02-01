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
            'project' => $project->only(['id','name','status']),
            'canManage' => $request->user()->can('create', [ProjectMedia::class, $project]),
            'media' => $media,
        ]);
    }

    public function store(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectMedia::class, $project]);

        $data = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:10240'], // 10MB each
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store("projects/{$project->id}/media", 'public');

            $media = ProjectMedia::create([
                'project_id' => $project->id,
                'uploaded_by' => $request->user()->id,
                'disk' => 'public',
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

    public function update(Request $request, Project $project, ProjectMedia $media, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($media->project_id === $project->id, 404);

        Gate::authorize('update', [$media, $project]);

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

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
        $media->delete();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'deleted', 'media', $before, null);

        return back()->with('success', 'File deleted.');
    }
}
