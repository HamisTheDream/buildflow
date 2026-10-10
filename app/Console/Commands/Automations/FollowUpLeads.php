<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\CRM\Lead;
use App\Models\Organization;
use App\Notifications\Automations\LeadFollowUpNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class FollowUpLeads extends Command
{
    protected $signature = 'automations:followup-leads';
    protected $description = 'Chase stale leads: nudge the rep after 2 days, re-engage the lead after 7';

    public function handle(): int
    {
        $nudged = 0;
        $reengaged = 0;
        $today = now()->startOfDay();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'lead_followup')) {
                continue;
            }

            $leads = Lead::where('organization_id', $org->id)
                ->where('status', 'new')
                ->whereDate('created_at', '<=', $today->copy()->subDays(2))
                ->with(['assignedTo', 'organization'])
                ->get();

            foreach ($leads as $lead) {
                $ageDays = (int) $today->diffInDays($lead->created_at->startOfDay());

                // 2+ days: nudge the assigned rep (once)
                if ($ageDays >= 2 && $lead->assignedTo
                    && ! AutomationLog::alreadyDid($org->id, 'lead_followup', 'rep_nudged', 'lead', $lead->id)) {
                    $lead->assignedTo->notify(new LeadFollowUpNotification($lead, 'nudge_rep'));
                    AutomationLog::record($org->id, 'lead_followup', 'rep_nudged', 'lead', $lead->id, [
                        'age_days' => $ageDays,
                        'rep' => $lead->assignedTo->email,
                    ]);
                    $nudged++;
                }

                // 7+ days: re-engagement email to the lead (once)
                if ($ageDays >= 7 && $lead->email
                    && ! AutomationLog::alreadyDid($org->id, 'lead_followup', 'lead_reengaged', 'lead', $lead->id)) {
                    Notification::route('mail', $lead->email)
                        ->notify(new LeadFollowUpNotification($lead, 'reengage_lead'));
                    AutomationLog::record($org->id, 'lead_followup', 'lead_reengaged', 'lead', $lead->id, [
                        'age_days' => $ageDays,
                    ]);
                    $reengaged++;
                }
            }
        }

        $this->info("Nudged {$nudged} reps, re-engaged {$reengaged} leads.");
        return self::SUCCESS;
    }
}
