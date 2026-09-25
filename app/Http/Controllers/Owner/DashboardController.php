<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Global Metrics
        $totalRevenueCents = DB::table('payments')
            ->where('status', 'success')
            ->whereNull('payments.deleted_at')
            ->sum('amount');
        
        $activeOrgs = Organization::where('subscription_status', 'active')->count();
        $totalUsers = User::count();
        $recentPayments = DB::table('payments')
            ->join('organizations', 'payments.organization_id', '=', 'organizations.id')
            ->select('payments.*', 'organizations.name as org_name')
            ->where('payments.status', 'success')
            ->whereNull('payments.deleted_at')
            ->whereNull('organizations.deleted_at')
            ->orderByDesc('payments.created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Owner/Dashboard', [
            'stats' => [
                'revenue_total_ngn' => $totalRevenueCents / 100,
                'active_organizations' => $activeOrgs,
                'total_users' => $totalUsers,
            ],
            'recentPayments' => $recentPayments->map(fn($p) => [
                'id' => $p->id,
                'amount_ngn' => $p->amount / 100,
                'org_name' => $p->org_name,
                'reference' => $p->reference,
                'date' => parse_date($p->created_at)->diffForHumans(),
            ]),
        ]);
    }
}
