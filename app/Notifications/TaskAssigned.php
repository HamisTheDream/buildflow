<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectTask $task
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
        $url = url("/app/projects/{$this->project->id}/tasks");

        return (new MailMessage)
            ->subject("BuildFlow: New task assigned — {$this->project->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You’ve been assigned a task on **{$this->project->name}**.")
            ->line("**Task:** {$this->task->title}")
            ->action('Open Tasks', $url)
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
