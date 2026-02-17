<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\ProjectCost;
use App\Models\ProjectLog;
use App\Models\ProjectTask;
use App\Models\CRM\Lead;
use App\Models\CRM\Deal;
use App\Models\Finance\Invoice;
use App\Models\Finance\Expense;
use App\Models\HR\Employee;
use App\Models\HR\Leave;
use App\Support\CurrentOrg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private const CACHE_TTL = 300;

    public function __invoke(Request $request)
    {
        $user = $request->user();
        $org = CurrentOrg::forUser($user);

        if (!$org) {
            return Inertia::render('App/Dashboard', [
                'stats' => null,
                'recentProjects' => [],
                'chartData' => null,
            ]);
        }

        $orgId = $org->id;
        $cacheKey = "dashboard_stats_{$orgId}";

        $stats = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($org) {
            return $this->computeStats($org);
        });

        // Chart data (cached separately, slightly longer TTL)
        $chartData = Cache::remember("dashboard_charts_{$orgId}", 600, function () use ($orgId) {
            return $this->computeChartData($orgId);
        });

        $recentProjects = Project::query()
            ->where('organization_id', $orgId)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get(['id', 'name', 'client_name', 'status', 'location', 'updated_at']);

        $recentActivity = ProjectLog::query()
            ->whereHas('project', fn($q) => $q->where('organization_id', $orgId))
            ->with(['project:id,name', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'project_id', 'user_id', 'title', 'body', 'type', 'created_at']);

        return Inertia::render('App/Dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentActivity' => $recentActivity,
            'chartData' => $chartData,
            'orgCurrency' => [
                'code' => $org->currency_code ?? 'NGN',
                'symbol' => $org->currency_symbol ?? '₦',
            ],
        ]);
    }

    private function computeStats($org): array
    {
        $orgId = $org->id;

        // Pre-fetch project IDs once to avoid repeated whereHas subqueries
        $projectIds = Project::where('organization_id', $orgId)->pluck('id');
        $projectsCount = $projectIds->count();

        $openIssuesCount = ProjectIssue::query()
            ->whereIn('project_id', $projectIds)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $pendingCostsSum = ProjectCost::query()
            ->whereIn('project_id', $projectIds)
            ->sum('amount');

        $recentActivityCount = ProjectLog::query()
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $overdueTasksCount = ProjectTask::query()
            ->whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->count();

        $tasksCount = ProjectTask::query()
            ->whereIn('project_id', $projectIds)
            ->count();

        $leadsCount = Lead::where('organization_id', $orgId)->count();
        $dealsCount = Deal::where('organization_id', $orgId)->count();

        $totalRevenue = Invoice::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->sum('total_amount_cents');

        $outstandingInvoices = Invoice::where('organization_id', $orgId)
            ->where('status', 'sent')
            ->sum('total_amount_cents');

        $totalExpenses = Expense::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount_cents');

        $employeesCount = Employee::where('organization_id', $orgId)->count();
        $pendingLeaves = Leave::where('organization_id', $orgId)
            ->where('status', 'pending')
            ->count();

        return [
            'projects_count' => $projectsCount,
            'open_issues_count' => $openIssuesCount,
            'pending_costs_sum' => $pendingCostsSum,
            'recent_activity_count' => $recentActivityCount,
            'overdue_tasks_count' => $overdueTasksCount,
            'tasks_count' => $tasksCount,
            'members_count' => $org->users()->count(),
            'leads_count' => $leadsCount,
            'deals_count' => $dealsCount,
            'total_revenue' => $totalRevenue,
            'outstanding_invoices' => $outstandingInvoices,
            'total_expenses' => $totalExpenses,
            'employees_count' => $employeesCount,
            'pending_leaves' => $pendingLeaves,
        ];
    }

    /**
     * Compute chart-specific data for the dashboard.
     */
    private function computeChartData(int $orgId): array
    {
        // 1. Project status breakdown
        $projectStatuses = Project::where('organization_id', $orgId)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2. Budget vs Actual spend (top 5 projects by cost)
        $topProjects = Project::where('organization_id', $orgId)
            ->withSum('costs as total_cost', 'amount')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get(['id', 'name', 'budget']);

        $budgetVsActual = [
            'labels' => $topProjects->pluck('name')->map(fn($n) => \Illuminate\Support\Str::limit($n, 15))->toArray(),
            'budget' => $topProjects->pluck('budget')->map(fn($v) => round(($v ?? 0) / 100))->toArray(),
            'actual' => $topProjects->pluck('total_cost')->map(fn($v) => round(($v ?? 0) / 100))->toArray(),
        ];

        // 3. Monthly activity trend (last 6 months) — single query with GROUP BY
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $projectIds = Project::where('organization_id', $orgId)->pluck('id');

        $monthlyCounts = ProjectLog::query()
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthLabels = [];
        $monthlyActivity = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthLabels[] = $date->format('M');
            $monthlyActivity[] = $monthlyCounts[$key] ?? 0;
        }

        // 4. Issue severity breakdown (reuse projectIds from above)
        $issueSeverities = ProjectIssue::query()
            ->whereIn('project_id', $projectIds)
            ->whereIn('status', ['open', 'in_progress', 'blocked'])
            ->selectRaw("severity, COUNT(*) as count")
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        return [
            'projectStatuses' => $projectStatuses,
            'budgetVsActual' => $budgetVsActual,
            'activityTrend' => [
                'labels' => $monthLabels,
                'data' => $monthlyActivity,
            ],
            'issueSeverities' => $issueSeverities,
        ];
    }

    public static function clearCache(int $orgId): void
    {
        Cache::forget("dashboard_stats_{$orgId}");
        Cache::forget("dashboard_charts_{$orgId}");
    }
}
