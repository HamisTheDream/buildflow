<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Organization;
use App\Models\ProjectTask;
use App\Notifications\Automations\TaskEscalationNotification;
use App\Notifications\Automations\TaskOverdueNotification;
use Illuminate\Console\Command;

class EscalateOverdueTasks extends Command
{
    protected $signature = 'automations:escalate-tasks';
    protected $description = 'Remind assignees of overdue tasks daily; escalate to managers after 3 days';

    public function handle(): int
    {
        $reminded = 0;
        $escalated = 0;
        $today = now()->startOfDay();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'task_escalation')) {
                continue;
            }

            $managers = $org->users()->wherePivotIn('role', ['owner', 'admin'])->get();

            $tasks = ProjectTask::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
                ->where('status', '!=', 'done')
                ->whereDate('due_date', '<', $today)
                ->with(['project', 'assignee'])
                ->get();

            foreach ($tasks as $task) {
                $days = (int) $today->diffInDays($task->due_date->startOfDay());

                // Daily nudge to the assignee (idempotent per day)
                $nudgeAction = 'overdue_nudge_' . $today->format('Y-m-d');
                if ($task->assignee && ! AutomationLog::alreadyDid($org->id, 'task_escalation', $nudgeAction, 'task', $task->id)) {
                    $task->assignee->notify(new TaskOverdueNotification($task->project, $task, $days));
                    AutomationLog::record($org->id, 'task_escalation', $nudgeAction, 'task', $task->id, [
                        'days_overdue' => $days,
                        'assignee' => $task->assignee->email,
                    ]);
                    $reminded++;
                }

                // Escalate to managers once, at 3+ days overdue
                if ($days >= 3 && ! AutomationLog::alreadyDid($org->id, 'task_escalation', 'escalated', 'task', $task->id)) {
                    foreach ($managers as $manager) {
                        $manager->notify(new TaskEscalationNotification(
                            $task->project, $task, $days, $task->assignee?->name
                        ));
                    }
                    AutomationLog::record($org->id, 'task_escalation', 'escalated', 'task', $task->id, [
                        'days_overdue' => $days,
                    ]);
                    $escalated++;
                }
            }
        }

        $this->info("Reminded {$reminded} assignees, escalated {$escalated} tasks.");
        return self::SUCCESS;
    }
}
