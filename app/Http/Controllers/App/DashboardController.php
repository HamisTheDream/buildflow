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
        $orgRole = $user->orgRole($orgId);

        // Get Department for non-owners
        $department = null;
        if (!in_array($orgRole, ['owner', 'admin'])) {
            $employee = Employee::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->with('department')
                ->first();
            $department = $employee?->department?->name;
        }

        // Cache key includes role and department to ensure correct data separation
        $cacheKey = "dashboard_stats_{$orgId}_{$orgRole}_" . ($department ?? 'none');

        $stats = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($org, $orgRole, $department) {
            return $this->computeStats($org, $orgRole, $department);
        });

        // Determine access for charts
        $dept = strtolower($department ?? '');
        $isManager = in_array($orgRole, ['owner', 'admin']);
        $isFinance = str_contains($dept, 'account') || str_contains($dept, 'finance');
        $showFinance = $isManager || $isFinance;

        // Chart data (cached separately, slightly longer TTL)
        $chartCacheKey = "dashboard_charts_{$orgId}_" . ($showFinance ? 'finance' : 'basic');

        $chartData = Cache::remember($chartCacheKey, 600, function () use ($orgId, $showFinance) {
            return $this->computeChartData($orgId, $showFinance);
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
            'userContext' => [
                'role' => $orgRole,
                'department' => $department,
            ]
        ]);
    }

    private function computeStats($org, $role, $department): array
    {
        $orgId = $org->id;
        $isManager = in_array($role, ['owner', 'admin']);

        // Departments (normalized)
        $dept = strtolower($department ?? '');
        $isSales = str_contains($dept, 'sales') || str_contains($dept, 'marketing');
        $isFinance = str_contains($dept, 'account') || str_contains($dept, 'finance');
        $isHR = str_contains($dept, 'hr') || str_contains($dept, 'human resource');

        // Pre-fetch project IDs once to avoid repeated whereHas subqueries
        $projectIds = Project::where('organization_id', $orgId)->pluck('id');
        $projectsCount = $projectIds->count();

        // Base Stats (Everyone sees these)
        $stats = [
            'projects_count' => $projectsCount,
            'tasks_count' => ProjectTask::whereIn('project_id', $projectIds)->count(),
            'recent_activity_count' => ProjectLog::whereIn('project_id', $projectIds)
                ->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        // Access Control Logic
        $showFinance = $isManager || $isFinance;
        $showHR = $isManager || $isHR;
        $showCRM = $isManager || $isSales;
        $showProjectDetails = $isManager || (!$isHR && !$isFinance); // Engineers/Standard users

        if ($showProjectDetails) {
            $stats['open_issues_count'] = ProjectIssue::whereIn('project_id', $projectIds)
                ->whereIn('status', ['open', 'in_progress'])->count();

            $stats['overdue_tasks_count'] = ProjectTask::whereIn('project_id', $projectIds)
                ->where('status', '!=', 'done')
                ->where('due_date', '<', Carbon::today())->count();
        }

        if ($showFinance || $showProjectDetails) {
            $stats['pending_costs_sum'] = ProjectCost::whereIn('project_id', $projectIds)->sum('amount');
        }

        if ($showCRM) {
            $stats['leads_count'] = Lead::where('organization_id', $orgId)->count();
            $stats['deals_count'] = Deal::where('organization_id', $orgId)->count();
        }

        if ($showFinance) {
            $stats['total_revenue'] = Invoice::where('organization_id', $orgId)
                ->where('status', 'paid')->sum('total_amount_cents');

            $stats['outstanding_invoices'] = Invoice::where('organization_id', $orgId)
                ->where('status', 'sent')->sum('total_amount_cents');

            $stats['total_expenses'] = Expense::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'paid'])->sum('amount_cents');
        }

        if ($showHR) {
            $stats['employees_count'] = Employee::where('organization_id', $orgId)->count();
            $stats['pending_leaves'] = Leave::where('organization_id', $orgId)
                ->where('status', 'pending')->count();
        }

        // Members Count (Visible to Managers and HR)
        if ($isManager || $isHR) {
            $stats['members_count'] = $org->users()->count();
        }

        return $stats;
    }

    /**
     * Compute chart-specific data for the dashboard.
     */
    private function computeChartData(int $orgId, bool $showFinance): array
    {
        // 1. Project status breakdown
        $projectStatuses = Project::where('organization_id', $orgId)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2. Budget vs Actual spend (Finance Only)
        $budgetVsActual = null;
        if ($showFinance) {
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
        }

        // 3. Monthly activity trend
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

        // 4. Issue severity breakdown
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
