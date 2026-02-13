<?php

namespace App\Services;

use App\Models\Project;

class ProjectStatisticsService
{
    /**
     * Get key metrics for a project's dashboard.
     *
     * @param Project $project
     * @return array
     */
    public function getProjectMetrics(Project $project): array
    {
        // Daily Pulse (today's activity)
        $today = now()->format('Y-m-d');
        $logsToday = $project->dailyLogs()->whereDate('log_date', $today)->count();
        $tasksCompletedToday = $project->tasks()->where('status', 'done')->whereDate('updated_at', $today)->count();

        // Action Items (needs attention)
        $openIssues = $project->issues()->where('status', 'open')->count();
        $overdueTasks = $project->tasks()->where('due_date', '<', now())->where('status', '!=', 'done')->count();

        // Financial Health (Budget vs Aggregated Costs)
        $totalBudget = (float) $project->budget;
        $totalSpend = $project->costs()->whereIn('status', ['approved', 'paid'])->sum('amount');

        return [
            'logs_today' => $logsToday,
            'tasks_completed_today' => $tasksCompletedToday,
            'open_issues' => $openIssues,
            'overdue_tasks' => $overdueTasks,
            'total_budget' => $totalBudget,
            'total_spend' => $totalSpend,
        ];
    }
}
