<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\UsageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrgBillingController extends Controller
{
    public function index(Request $request, UsageService $usage)
    {
        $org = $request->user()->currentOrganization;
        abort_unless($org, 403);

        $gate = $usage->withinLimits($org);

        $plans = Plan::orderBy('id')->get()->map(fn($p) => [
            'key' => $p->key,
            'name' => $p->name,
            'price_monthly_cents' => $p->price_monthly_cents,
            'max_projects' => $p->max_projects,
            'max_members' => $p->max_members,
            'max_storage_mb' => $p->max_storage_mb,
            'can_password_protect_reports' => $p->can_password_protect_reports,
        ]);

        return Inertia::render('App/Billing/Index', [
            'organization' => [
                'id' => $org->id,
                'name' => $org->name,
                'subscription_status' => $org->subscription_status,
                'trial_ends_at' => $org->trial_ends_at?->toDateTimeString(),
                'plan' => $org->effectivePlan()->key,
            ],
            'plans' => $plans,
            'gate' => $gate,
        ]);
    }
}
