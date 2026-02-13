<?php

namespace App\Notifications;

use App\Models\ProjectTask;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProjectTask $task,
        public Project $project,
        public string $assignedByName = 'Someone'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}?tab=tasks");

        return (new MailMessage)
            ->subject("New Task: {$this->task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->assignedByName} assigned you a task on **{$this->project->name}**.")
            ->line("**Task:** {$this->task->title}")
            ->line($this->task->description ? "**Details:** {$this->task->description}" : '')
            ->action('View Task', $url)
            ->line('Thanks for using BuildFlow!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'title' => "New task assigned: {$this->task->title}",
            'body' => "{$this->assignedByName} assigned you a task on {$this->project->name}",
            'data' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'task_id' => $this->task->id,
                'task_title' => $this->task->title,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
