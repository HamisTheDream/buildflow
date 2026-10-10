<?php

namespace App\Notifications\Automations;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskEscalationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectTask $task,
        public int $daysOverdue,
        public ?string $assigneeName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}/tasks");
        $who = $this->assigneeName ?? 'Unassigned';

        return (new MailMessage)
            ->subject("BuildFlow: ESCALATED — task {$this->daysOverdue} days overdue")
            ->greeting("Hi {$notifiable->name},")
            ->line("**{$this->task->title}** on **{$this->project->name}** (owner: {$who}) is **{$this->daysOverdue} days overdue** and needs management attention.")
            ->action('Open Tasks', $url)
            ->line('BuildFlow Automations');
    }
}
