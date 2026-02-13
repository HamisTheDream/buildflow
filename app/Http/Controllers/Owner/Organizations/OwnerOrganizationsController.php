<?php

namespace App\Http\Controllers\Owner\Organizations;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OwnerOrganizationsController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');
        $planKey = (string) $request->query('plan', '');

        $orgs = Organization::query()
            ->with(['plan:id,key,name'])
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
                // add more fields if you store them: brand_email, brand_phone, etc.
            })
            ->when($status, fn($query) => $query->where('subscription_status', $status))
            ->when($planKey, function ($query) use ($planKey) {
                $query->whereHas('plan', fn($p) => $p->where('key', $planKey));
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($o) {
                return [
                    'id' => $o->id,
                    'name' => $o->name,
                    'plan' => $o->plan ? [
                        'key' => $o->plan->key,
                        'name' => $o->plan->name,
                    ] : null,
                    'subscription_status' => $o->subscription_status,
                    'trial_ends_at' => $o->trial_ends_at?->toDateTimeString(),
                    'paid_until' => $o->paid_until?->toDateTimeString(),
                    'created_at' => $o->created_at->toDateTimeString(),
                    'brand_email' => $o->brand_email,
                    'brand_logo_path' => $o->brand_logo_path ? asset('storage/' . $o->brand_logo_path) : null,
                ];
            });

        $plans = Plan::query()->orderBy('id')->get(['id', 'key', 'name']);

        $metrics = [
            'total' => Organization::count(),
            'active' => Organization::where('subscription_status', 'active')->count(),
            'trial' => Organization::where('subscription_status', 'trial')->count(),
            'expired' => Organization::whereIn('subscription_status', ['past_due', 'suspended', 'expired'])->count(),
        ];

        return Inertia::render('Owner/Organizations/Index', [
            'filters' => [
                'q' => $q,
                'status' => $status,
                'plan' => $planKey,
            ],
            'plans' => $plans,
            'organizations' => $orgs,
            'metrics' => $metrics,
        ]);
    }

    public function show(Request $request, Organization $organization)
    {
        $organization->load(['plan:id,key,name']);

        // Counts (pivot + simple relations)
        $membersCount = $organization->users()->count();
        $projectsCount = method_exists($organization, 'projects')
            ? $organization->projects()->count()
            : 0;

        // Revenue + last payment
        $lastPayment = Payment::where('organization_id', $organization->id)
            ->orderByDesc('id')
            ->first();

        // Unified Timeline Query
        // We select common columns: id, type (formatted), content, admin_name, admin_avatar, created_at
        // This avoids N+1 and massive payloads

        $orgId = $organization->id;

        $notesQuery = \Illuminate\Support\Facades\DB::table('organization_notes')
            ->selectRaw("
                id, 
                type as activity_type, 
                content as description, 
                created_at, 
                admin_id, 
                'note' as source_table
            ")
            ->where('organization_id', $orgId);

        $auditsQuery = \Illuminate\Support\Facades\DB::table('admin_audit_logs')
            ->selectRaw("
                id, 
                'system' as activity_type, 
                CONCAT(action, ': ', IFNULL(reason, '')) as description, 
                created_at, 
                admin_id, 
                'audit' as source_table
            ")
            ->where('subject_type', 'Organization')
            ->where('subject_id', $orgId);

        $paymentsQuery = \Illuminate\Support\Facades\DB::table('payments')
            ->selectRaw("
                id, 
                'system' as activity_type, 
                CONCAT('Payment ', status, ': ', currency, ' ', amount_cents / 100) as description, 
                created_at, 
                NULL as admin_id, 
                'payment' as source_table
            ")
            ->where('organization_id', $orgId);

        $timeline = $notesQuery
            ->union($auditsQuery)
            ->union($paymentsQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Pivot to load admin details efficiently
        $adminIds = collect($timeline->items())->pluck('admin_id')->filter()->unique();
        $admins = \App\Models\Admin::whereIn('id', $adminIds)->get()->keyBy('id');

        // Transform for frontend
        $timeline->getCollection()->transform(function ($item) use ($admins) {
            $admin = $item->admin_id ? $admins->get($item->admin_id) : null;
            return [
                'id' => $item->source_table . '-' . $item->id, // Unique key
                'type' => $item->activity_type,
                'content' => $item->description,
                'created_at' => \Carbon\Carbon::parse($item->created_at)->toDateTimeString(),
                'admin' => $admin ? [
                    'name' => $admin->name,
                    'avatar_url' => $admin->avatar_path ? asset('storage/' . $admin->avatar_path) : null,
                ] : ['name' => 'System'],
            ];
        });

        // Tasks (kept separate as they are a sidebar operational list)
        $tasks = $organization->crmTasks()
            ->whereNull('completed_at')
            ->with(['assignee:id,name,avatar_path', 'creator:id,name'])
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'content' => $t->content,
                'due_at' => $t->due_at?->toDateTimeString(),
                'assigned_to' => $t->assignee ? [
                    'id' => $t->assignee->id,
                    'name' => $t->assignee->name,
                    'avatar_url' => $t->assignee->avatar_path ? asset('storage/' . $t->assignee->avatar_path) : null,
                ] : null,
                'created_by' => $t->creator->name,
            ]);

        $plans = Plan::orderBy('id')->get(['id', 'key', 'name']);

        return Inertia::render('Owner/Organizations/Show', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'plan' => $organization->plan ? [
                    'key' => $organization->plan->key,
                    'name' => $organization->plan->name,
                ] : null,
                'subscription_status' => $organization->subscription_status,
                'crm_stage' => $organization->crm_stage,
                'trial_ends_at' => $organization->trial_ends_at?->toDateTimeString(),
                'paid_until' => $organization->paid_until?->toDateTimeString(),
                'created_at' => $organization->created_at->toDateTimeString(),
                'logo_url' => $organization->brand_logo_path ? asset('storage/' . $organization->brand_logo_path) : null,
                'owner' => $organization->ownerUser() ? [
                    'name' => $organization->ownerUser()->name,
                    'email' => $organization->ownerUser()->email,
                ] : null,
            ],
            'stats' => [
                'members' => $membersCount,
                'projects' => $projectsCount,
                'last_payment_status' => $lastPayment?->status,
                'last_payment_amount_kobo' => $lastPayment ? (int)$lastPayment->amount_cents : 0,
                'last_payment_at' => $lastPayment?->created_at?->toDateTimeString(),
            ],
            'timeline' => $timeline,
            'tasks' => $tasks,
            'plans' => $plans,
        ]);
    }

    public function extendTrial(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:90'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $organization->subscription_status = 'trial';
        $organization->trial_ends_at = now()->addDays($data['days']);
        // keep paid_until as-is; trial overrides in isActiveAccess logic
        $organization->save();

        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $audit->log(
            $admin->id,
            'org.extend_trial',
            $organization,
            $before,
            $after,
            $data['reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', "Trial extended by {$data['days']} days.");
    }

    public function compPlan(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        // optional: only super admins can comp
        if (!$admin->is_super) {
            return back()->with('error', 'Only super admins can comp plans.');
        }

        $data = $request->validate([
            'plan_key' => ['required', 'string'],
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $plan = Plan::where('key', $data['plan_key'])->firstOrFail();

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $organization->plan_id = $plan->id;
        $organization->subscription_status = 'active';
        $organization->trial_ends_at = null;

        $start = ($organization->paid_until && now()->lessThanOrEqualTo($organization->paid_until))
            ? $organization->paid_until
            : now();

        $organization->paid_until = $start->copy()->addDays($data['days']);
        $organization->save();

        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $audit->log(
            $admin->id,
            'org.comp_plan',
            $organization,
            $before,
            $after,
            $data['reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', "Comped {$plan->name} for {$data['days']} days.");
    }

    public function downgrade(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        if (!$admin->is_super) {
            return back()->with('error', 'Only super admins can downgrade accounts.');
        }

        $data = $request->validate([
            'plan_key' => ['required', 'string'], // e.g. free
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $plan = Plan::where('key', $data['plan_key'])->firstOrFail();

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $organization->plan_id = $plan->id;
        $organization->subscription_status = 'free';
        $organization->trial_ends_at = null;
        $organization->paid_until = null;
        $organization->save();

        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $audit->log(
            $admin->id,
            'org.downgrade',
            $organization,
            $before,
            $after,
            $data['reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', "Organization downgraded to {$plan->name}.");
    }

    public function suspend(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        if (!$admin->is_super) {
            return back()->with('error', 'Only super admins can suspend organizations.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $organization->subscription_status = 'suspended';
        $organization->save();

        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $audit->log(
            $admin->id,
            'org.suspend',
            $organization,
            $before,
            $after,
            $data['reason'],
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Organization suspended.');
    }

    public function reactivate(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        if (!$admin->is_super) {
            return back()->with('error', 'Only super admins can reactivate organizations.');
        }

        $data = $request->validate([
            'mode' => ['required', 'in:trial,active,free'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        if ($data['mode'] === 'trial') {
            $organization->subscription_status = 'trial';
            $organization->trial_ends_at = now()->addDays($data['days'] ?? 7);
        } elseif ($data['mode'] === 'active') {
            $organization->subscription_status = 'active';
            $organization->trial_ends_at = null;
            $organization->paid_until = now()->addDays($data['days'] ?? 30);
        } else {
            $organization->subscription_status = 'free';
            $organization->trial_ends_at = null;
            $organization->paid_until = null;
        }

        $organization->save();

        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id']);

        $audit->log(
            $admin->id,
            'org.reactivate',
            $organization,
            $before,
            $after,
            $data['reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Organization reactivated.');
    }
    public function updateStage(Request $request, Organization $organization, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'stage' => ['required', 'in:lead,onboarding,active,risk,churned'],
        ]);

        $before = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id', 'crm_stage']);
        $organization->crm_stage = $data['stage'];
        $organization->save();
        $after = $organization->only(['subscription_status', 'trial_ends_at', 'paid_until', 'plan_id', 'crm_stage']);

        $audit->log(
            $admin->id,
            'org.crm_update_stage',
            $organization,
            $before,
            $after,
            null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'CRM stage updated.');
    }
}
