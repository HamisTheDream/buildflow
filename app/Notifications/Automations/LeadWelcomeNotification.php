<?php

namespace App\Notifications\Automations;

use App\Models\CRM\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $org = $this->lead->organization;
        $name = trim($this->lead->first_name . ' ' . ($this->lead->last_name ?? ''));

        return (new MailMessage)
            ->subject("Thanks for reaching out to {$org->name}")
            ->greeting("Hi {$name},")
            ->line("Thanks for your interest in **{$org->name}**. A member of our team will be in touch with you shortly.")
            ->line("— {$org->name} (via BuildFlow)");
    }
}
