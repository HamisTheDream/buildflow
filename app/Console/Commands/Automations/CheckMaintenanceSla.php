<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Organization;
use App\Models\ProjectIssue;
use App\Notifications\Automations\SlaBreachNotification;
use Illuminate\Console\Command;

class CheckMaintenanceSla extends Command
{
    protected $signature = 'automations:maintenance-sla';
    protected $description = 'Escalate maintenance tickets that breach their severity SLA';

    /** SLA hours by severity. */
    private const SLA_HOURS = [
        'urgent' => 24,
        'high' => 72,
        'medium' => 168, // 7 days
        'low' => 336,    // 14 days
    ];

    public function handle(): int
    {
        $breaches = 0;
        $now = now();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'maintenance_sla')) {
                continue;
            }

            $managers = $org->users()->wherePivotIn('role', ['owner', 'admin'])->get();

            $issues = ProjectIssue::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
                ->whereIn('status', ['open', 'in_progress'])
                ->with(['project', 'assignee'])
                ->get();

            foreach ($issues as $issue) {
                $sla = self::SLA_HOURS[strtolower($issue->severity ?? 'medium')] ?? self::SLA_HOURS['medium'];
                $hoursOpen = (int) $now->diffInHours($issue->created_at);

                if ($hoursOpen < $sla) {
                    continue;
                }

                if (AutomationLog::alreadyDid($org->id, 'maintenance_sla', 'sla_breached', 'issue', $issue->id)) {
                    continue;
                }

                $recipients = collect([$issue->assignee])->filter()->merge($managers)->unique('id');
                foreach ($recipients as $recipient) {
                    $recipient->notify(new SlaBreachNotification($issue->project, $issue, $sla, $hoursOpen));
                }

                AutomationLog::record($org->id, 'maintenance_sla', 'sla_breached', 'issue', $issue->id, [
                    'severity' => $issue->severity,
                    'sla_hours' => $sla,
                    'hours_open' => $hoursOpen,
                ]);

                $breaches++;
            }
        }

        $this->info("Escalated {$breaches} SLA breaches.");
        return self::SUCCESS;
    }
}
