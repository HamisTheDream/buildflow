<?php

namespace App\Notifications\Automations;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectTask $task,
        public int $daysOverdue,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}/tasks");

        return (new MailMessage)
            ->subject("BuildFlow: Task overdue — {$this->task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("The task **{$this->task->title}** on **{$this->project->name}** is **{$this->daysOverdue} day(s) overdue** (was due {$this->task->due_date->format('d M Y')}).")
            ->action('Open Tasks', $url)
            ->line('BuildFlow Automations');
    }
}
