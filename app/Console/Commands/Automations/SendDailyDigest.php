<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\CRM\Lead;
use App\Models\Finance\Invoice;
use App\Models\HR\Leave;
use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectIssue;
use App\Models\ProjectTask;
use App\Notifications\Automations\DailyDigestNotification;
use Illuminate\Console\Command;

class SendDailyDigest extends Command
{
    protected $signature = 'automations:daily-digest';
    protected $description = 'Morning briefing email for org owners/admins';

    public function handle(): int
    {
        $sent = 0;
        $today = now()->startOfDay();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'daily_digest')) {
                continue;
            }

            $managers = $org->users()->wherePivotIn('role', ['owner', 'admin'])->get();
            if ($managers->isEmpty()) {
                continue;
            }

            $projectIds = Project::where('organization_id', $org->id)->pluck('id');

            $overdueTasks = ProjectTask::whereIn('project_id', $projectIds)
                ->where('status', '!=', 'done')
                ->whereDate('due_date', '<', $today)
                ->count();

            $openIssues = ProjectIssue::whereIn('project_id', $projectIds)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            $unpaidInvoices = Invoice::where('organization_id', $org->id)
                ->whereIn('status', ['sent', 'overdue']);

            $budgetAlerts = 0;
            foreach (Project::where('organization_id', $org->id)->where('budget', '>', 0)->get(['id', 'budget']) as $project) {
                $spent = (int) ProjectCost::where('project_id', $project->id)->sum('amount');
                if ($project->budget > 0 && $spent / (float) $project->budget >= 0.8) {
                    $budgetAlerts++;
                }
            }

            $stats = [
                'overdue_tasks' => $overdueTasks,
                'open_issues' => $openIssues,
                'unpaid_invoices_count' => (clone $unpaidInvoices)->count(),
                'unpaid_invoices_cents' => (int) (clone $unpaidInvoices)->sum('total_amount_cents'),
                'budget_alerts' => $budgetAlerts,
                'pending_leaves' => Leave::where('organization_id', $org->id)->where('status', 'pending')->count(),
                'new_leads' => Lead::where('organization_id', $org->id)
                    ->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            foreach ($managers as $manager) {
                $manager->notify(new DailyDigestNotification($org, $stats));
            }

            AutomationLog::record($org->id, 'daily_digest', 'digest_sent', null, null, $stats);
            $sent++;
        }

        $this->info("Sent daily digest to {$sent} organizations.");
        return self::SUCCESS;
    }
}
