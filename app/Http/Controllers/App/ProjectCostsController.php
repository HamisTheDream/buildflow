<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use App\Services\ActivityLogger;

class ProjectCostsController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $categories = ['material','labor','equipment','transport','misc','service','permit','fuel','security'];
        $payments = ['cash','transfer','card','cheque','other'];

        $filters = $request->validate([
            'category' => ['nullable', 'in:'.implode(',', $categories)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $query = ProjectCost::query()
            ->where('project_id', $project->id)
            ->with([
                'creator:id,name,email',
                'attachments.uploader:id,name,email',
            ]);

        if (!empty($filters['category'])) $query->where('category', $filters['category']);
        if (!empty($filters['from'])) $query->where('cost_date', '>=', $filters['from']);
        if (!empty($filters['to'])) $query->where('cost_date', '<=', $filters['to']);
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('vendor', 'like', "%{$q}%")
                   ->orWhere('reference', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // totals
        $filteredTotal = (clone $query)->sum('amount');

        $byCategory = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'total' => (float)$r->total,
            ]);

        $costs = $query
            ->orderByDesc('cost_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $costs->getCollection()->transform(fn ($c) => [
            'id' => $c->id,
            'cost_date' => $c->cost_date->toDateString(),
            'category' => $c->category,
            'vendor' => $c->vendor,
            'payment_method' => $c->payment_method,
            'amount' => (float)$c->amount,
            'reference' => $c->reference,
            'description' => $c->description,
            'created_at' => $c->created_at->toDateTimeString(),
            'creator' => $c->creator ? $c->creator->only('id','name','email') : null,
            'attachments' => $c->attachments->map(fn($a) => [
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

        return Inertia::render('App/Projects/Costs', [
            'project' => $project->only(['id','name','status']),
            'canManage' => $request->user()->can('create', [ProjectCost::class, $project]),
            'categories' => $categories,
            'payments' => $payments,
            'filters' => [
                'category' => $request->query('category', ''),
                'from' => $request->query('from', ''),
                'to' => $request->query('to', ''),
                'q' => $request->query('q', ''),
            ],
            'totals' => [
                'filtered_total' => (float)$filteredTotal,
                'by_category' => $byCategory,
            ],
            'costs' => $costs,
        ]);
    }

    public function store(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectCost::class, $project]);

        $categories = ['material','labor','equipment','transport','misc','service','permit','fuel','security'];
        $payments = ['cash','transfer','card','cheque','other'];

        $data = $request->validate([
            'cost_date' => ['required', 'date'],
            'category' => ['required', 'in:'.implode(',', $categories)],
            'vendor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:'.implode(',', $payments)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $cost = ProjectCost::create([
            'project_id' => $project->id,
            'created_by' => $request->user()->id,
            ...$data,
        ]);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'created', 'costs', null, $cost);

        return back()->with('success', 'Cost added.');
    }

    public function update(Request $request, Project $project, ProjectCost $cost, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($cost->project_id === $project->id, 404);
        Gate::authorize('update', [$cost, $project]);

        $categories = ['material','labor','equipment','transport','misc','service','permit','fuel','security'];
        $payments = ['cash','transfer','card','cheque','other'];

        $data = $request->validate([
            'cost_date' => ['required', 'date'],
            'category' => ['required', 'in:'.implode(',', $categories)],
            'vendor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:'.implode(',', $payments)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $before = $cost->replicate();
        $cost->update($data);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'updated', 'costs', $before, $cost);

        return back()->with('success', 'Cost updated.');
    }

    public function destroy(Request $request, Project $project, ProjectCost $cost, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($cost->project_id === $project->id, 404);
        Gate::authorize('delete', [$cost, $project]);

        $before = $cost->replicate();
        $cost->delete();

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'deleted', 'costs', $before, null);

        return back()->with('success', 'Cost deleted.');
    }
}
