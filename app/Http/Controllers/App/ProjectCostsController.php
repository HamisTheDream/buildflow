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

        $categories = ['material', 'labor', 'equipment', 'transport', 'misc', 'service', 'permit', 'fuel', 'security'];
        $payments = ['cash', 'transfer', 'card', 'cheque', 'other'];
        $statuses = ['pending', 'approved', 'paid', 'rejected'];

        $filters = $request->validate([
            'category' => ['nullable', 'in:' . implode(',', $categories)],
            'status' => ['nullable', 'in:' . implode(',', $statuses)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'unit' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $query = ProjectCost::query()
            ->where('project_id', $project->id)
            ->with([
                'creator:id,name,email',
                'unit:id,name',
                'attachments.uploader:id,name,email',
            ]);

        if (!empty($filters['category'])) $query->where('category', $filters['category']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['from'])) $query->where('cost_date', '>=', $filters['from']);
        if (!empty($filters['to'])) $query->where('cost_date', '<=', $filters['to']);
        if (!empty($filters['unit'])) $query->where('project_unit_id', (int)$filters['unit']);
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('vendor', 'like', "%{$q}%")
                    ->orWhere('reference', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // --- Decision Metrics ---
        // 1. Pending Approvals
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $pendingAmount = (clone $query)->where('status', 'pending')->sum('amount');

        // 2. Unpaid but Approved (Accounts Payable)
        $unpaidCount = (clone $query)->where('status', 'approved')->count();
        $unpaidAmount = (clone $query)->where('status', 'approved')->sum('amount');

        // 3. Total Spend (Approved Only) vs Budget
        // Note: We use the *unfiltered* total for the project budget bar, not the filtered query
        $rawProjectCosts = ProjectCost::where('project_id', $project->id);
        $totalSpend = (clone $rawProjectCosts)->whereIn('status', ['approved', 'paid'])->sum('amount');
        $totalBudget = (float) $project->budget;

        $byCategory = (clone $rawProjectCosts)
            ->selectRaw('category, SUM(amount) as total')
            ->whereIn('status', ['approved', 'paid'])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'category' => $r->category,
                'total' => (float)$r->total,
            ]);

        $costs = $query
            ->orderByDesc('cost_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $costs->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'cost_date' => $c->cost_date->toDateString(),
            'category' => $c->category,
            'vendor' => $c->vendor,
            'payment_method' => $c->payment_method,
            'amount' => (float)$c->amount,
            'reference' => $c->reference,
            'description' => $c->description,
            'status' => $c->status,
            'is_paid' => $c->status === 'paid',
            'rejection_reason' => $c->rejection_reason,
            'project_unit_id' => $c->project_unit_id,
            'unit' => $c->unit ? $c->unit->only('id', 'name') : null,
            'created_at' => $c->created_at->toDateTimeString(),
            'creator' => $c->creator ? $c->creator->only('id', 'name', 'email') : null,
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

        $units = $project->units()->orderBy('name')->select('id', 'name')->get();

        return Inertia::render('App/Projects/Costs', [
            'project' => $project->only(['id', 'name', 'status']),
            'canManage' => $request->user()->can('create', [ProjectCost::class, $project]),
            'categories' => $categories,
            'payments' => $payments,
            'statuses' => $statuses,
            'units' => $units,
            'filters' => [
                'category' => $request->query('category', ''),
                'status' => $request->query('status', ''),
                'from' => $request->query('from', ''),
                'to' => $request->query('to', ''),
                'unit' => $request->query('unit', ''),
                'q' => $request->query('q', ''),
            ],
            'metrics' => [
                'pending_count' => $pendingCount,
                'pending_amount' => (float)$pendingAmount,
                'unpaid_count' => $unpaidCount,
                'unpaid_amount' => (float)$unpaidAmount,
                'total_spend' => (float)$totalSpend,
                'total_budget' => (float)$totalBudget,
            ],
            'analysis' => [
                'by_category' => $byCategory,
            ],
            'costs' => $costs,
        ]);
    }

    public function store(\App\Http\Requests\StoreCostRequest $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('create', [ProjectCost::class, $project]);

        $data = $request->validated();

        $cost = ProjectCost::create([
            'project_id' => $project->id,
            'created_by' => $request->user()->id,
            ...$data,
        ]);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'created', 'costs', null, $cost);

        return back()->with('success', 'Cost added.');
    }

    public function update(\App\Http\Requests\StoreCostRequest $request, Project $project, ProjectCost $cost, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        abort_unless($cost->project_id === $project->id, 404);
        Gate::authorize('update', [$cost, $project]);

        $data = $request->validated();

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
