<?php

namespace App\Notifications\CRM;

use App\Models\OrganizationTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public OrganizationTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orgName = $this->task->organization->name;
        $creatorName = $this->task->creator->name;

        return (new MailMessage)
            ->subject("New Task Assigned: {$orgName}")
            ->line("{$creatorName} assigned you a task for {$orgName}.")
            ->line("Task: \"{$this->task->content}\"")
            ->action('View Organization', url("/owner/organizations/{$this->task->organization_id}"))
            ->line('Please complete this task by the due date.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'organization_id' => $this->task->organization_id,
            'content' => $this->task->content,
            'created_by' => $this->task->created_by,
            'type' => 'task_assigned',
        ];
    }
}
