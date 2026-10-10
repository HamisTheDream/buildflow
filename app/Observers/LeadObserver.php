<?php

namespace App\Observers;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\CRM\Lead;
use App\Notifications\Automations\LeadWelcomeNotification;
use Illuminate\Support\Facades\Notification;

class LeadObserver
{
    /**
     * New lead: auto-assign round-robin to a sales rep (fallback: owner),
     * send the welcome email, and log it. Honors the lead_followup toggle.
     */
    public function created(Lead $lead): void
    {
        $orgId = $lead->organization_id;

        if (! AutomationSetting::isEnabled($orgId, 'lead_followup')) {
            return;
        }

        $org = $lead->organization;
        if (! $org) {
            return;
        }

        // Round-robin across sales-capable users: sales_agent → admin → owner
        $candidates = $org->users()
            ->wherePivotIn('role', ['sales_agent', 'admin', 'owner'])
            ->orderBy('users.id')
            ->get();

        if ($candidates->isNotEmpty()) {
            $lastAssignedId = (int) cache()->get("lead_robin_{$orgId}", 0);
            $next = $candidates->firstWhere('id', '>', $lastAssignedId) ?? $candidates->first();
            $lead->update(['assigned_to' => $next->id]);
            cache()->put("lead_robin_{$orgId}", $next->id, now()->addDay());
        }

        if ($lead->email) {
            Notification::route('mail', $lead->email)->notify(new LeadWelcomeNotification($lead));
        }

        AutomationLog::record($orgId, 'lead_followup', 'lead_welcomed', 'lead', $lead->id, [
            'assigned_to' => $lead->assigned_to,
            'email_sent' => (bool) $lead->email,
        ]);
    }
}
