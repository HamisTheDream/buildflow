<?php

namespace App\Notifications\Automations;

use App\Models\CRM\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadFollowUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Lead $lead,
        public string $kind, // 'nudge_rep' | 'reengage_lead'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $org = $this->lead->organization;
        $leadName = trim($this->lead->first_name . ' ' . ($this->lead->last_name ?? ''));
        $url = url('/app/crm');

        if ($this->kind === 'nudge_rep') {
            return (new MailMessage)
                ->subject("BuildFlow: Lead going cold — {$leadName}")
                ->greeting("Hi {$notifiable->name},")
                ->line("Lead **{$leadName}** has been sitting in **new** status with no activity. Time to follow up.")
                ->action('Open CRM', $url)
                ->line('BuildFlow Automations');
        }

        return (new MailMessage)
            ->subject("Still interested? — {$org->name}")
            ->greeting("Hi {$leadName},")
            ->line("We noticed you reached out a little while ago and wanted to check in — is there anything we can help you with?")
            ->line("Just reply to this email and we'll pick it right up.")
            ->line("— {$org->name} (via BuildFlow)");
    }
}
