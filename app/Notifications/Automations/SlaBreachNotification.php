<?php

namespace App\Notifications\Automations;

use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectIssue $issue,
        public int $slaHours,
        public int $hoursOpen,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}/issues");

        return (new MailMessage)
            ->subject("BuildFlow: SLA breached — {$this->issue->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Ticket **{$this->issue->title}** on **{$this->project->name}** has been open for **{$this->hoursOpen}h**, past its **{$this->slaHours}h** SLA ({$this->issue->severity}).")
            ->action('Open Issues', $url)
            ->line('BuildFlow Automations');
    }
}
