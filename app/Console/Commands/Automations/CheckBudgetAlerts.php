<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Notifications\Automations\BudgetAlertNotification;
use Illuminate\Console\Command;

class CheckBudgetAlerts extends Command
{
    protected $signature = 'automations:budget-alerts';
    protected $description = 'Alert owners when project spend crosses 80% / 100% of budget';

    public function handle(): int
    {
        $alerts = 0;

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'budget_alerts')) {
                continue;
            }

            $managers = $org->users()->wherePivotIn('role', ['owner', 'admin'])->get();
            if ($managers->isEmpty()) {
                continue;
            }

            $projects = Project::where('organization_id', $org->id)
                ->whereNotNull('budget')
                ->where('budget', '>', 0)
                ->get(['id', 'name', 'budget', 'organization_id']);

            foreach ($projects as $project) {
                $spent = (int) ProjectCost::where('project_id', $project->id)->sum('amount');
                $pct = $spent / (float) $project->budget * 100;

                foreach ([100, 80] as $threshold) {
                    if ($pct < $threshold) {
                        continue;
                    }

                    $action = "budget_alert_{$threshold}";
                    if (AutomationLog::alreadyDid($org->id, 'budget_alerts', $action, 'project', $project->id)) {
                        continue;
                    }

                    foreach ($managers as $manager) {
                        $manager->notify(new BudgetAlertNotification($project, $threshold, $spent, (float) $project->budget));
                    }

                    AutomationLog::record($org->id, 'budget_alerts', $action, 'project', $project->id, [
                        'threshold' => $threshold,
                        'spent' => $spent,
                        'budget' => (float) $project->budget,
                        'percent' => round($pct, 1),
                    ]);

                    $alerts++;
                    break; // one alert per project per run (highest threshold first)
                }
            }
        }

        $this->info("Sent {$alerts} budget alerts.");
        return self::SUCCESS;
    }
}
