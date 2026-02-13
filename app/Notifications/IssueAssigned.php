<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectIssue $issue
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
        $url = url("/app/projects/{$this->project->id}/issues");

        return (new MailMessage)
            ->subject("BuildFlow: New issue assigned — {$this->project->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You’ve been assigned an issue on **{$this->project->name}**.")
            ->line("**Issue:** {$this->issue->title}")
            ->line("**Severity:** " . ucfirst($this->issue->severity))
            ->action('Open Issues', $url)
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
