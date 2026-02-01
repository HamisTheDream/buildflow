<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGenerated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public object $project,
        public object $report
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}/reports");

        return (new MailMessage)
            ->subject("BuildFlow: Report generated — {$this->project->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A report was generated for **{$this->project->name}**.")
            ->line("**Report:** " . ($this->report->title ?: "Report #{$this->report->id}"))
            ->line("**Period:** {$this->report->from_date->toDateString()} → {$this->report->to_date->toDateString()}")
            ->action('View Reports', $url)
            ->line('BuildFlow');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
