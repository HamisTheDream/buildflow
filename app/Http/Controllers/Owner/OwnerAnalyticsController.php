<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // For real-time metrics, we calculate ranges
        $now = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();

        // 1. Projects Created
        $totalProjects = Project::count();
        $projectsThisMonth = Project::where('created_at', '>=', $thisMonth)->count();

        // 2. Active Users vs Total
        $totalUsers = User::count();
        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        // 3. Subscriptions breakdown
        $orgStatusCounts = Organization::select('subscription_status', DB::raw('count(*) as total'))
            ->groupBy('subscription_status')
            ->pluck('total', 'subscription_status')
            ->toArray();

        // 4. Mixpanel configuration status
        $mixpanelToken = config('services.mixpanel.token', env('VITE_MIXPANEL_TOKEN'));

        // 5. Growth Data for Charts (Last 12 months for deeper analytics than the dashboard)
        $monthlySignups = collect(range(11, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            return [
                'month' => $start->format('M Y'),
                'organizations' => Organization::whereBetween('created_at', [$start, $end])->count(),
                'users' => User::whereBetween('created_at', [$start, $end])->count(),
            ];
        })->values()->toArray();

        $monthlyRevenue = collect(range(11, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            $revenue = Payment::where('status', 'success')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount_cents');
            return [
                'month' => $start->format('M Y'),
                'revenue_ngn' => (int) $revenue,
            ];
        })->values()->toArray();

        // Pass to Vue
        return Inertia::render('Owner/Analytics/Index', [
            'metrics' => [
                'total_projects' => $totalProjects,
                'projects_this_month' => $projectsThisMonth,
                'total_users' => $totalUsers,
                'users_this_month' => $usersThisMonth,
            ],
            'org_status_distribution' => $orgStatusCounts,
            'charts' => [
                'growth' => $monthlySignups,
                'revenue' => $monthlyRevenue,
            ],
            'mixpanel' => [
                'configured' => !empty($mixpanelToken),
                'token' => $mixpanelToken ? substr($mixpanelToken, 0, 8) . '...' : null,
            ]
        ]);
    }
}
