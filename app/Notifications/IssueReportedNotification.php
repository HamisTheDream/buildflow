<?php

namespace App\Notifications;

use App\Models\ProjectIssue;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProjectIssue $issue,
        public Project $project,
        public string $reportedByName = 'Someone'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/app/projects/{$this->project->id}?tab=issues");
        $severity = ucfirst($this->issue->severity ?? 'medium');

        return (new MailMessage)
            ->subject("[{$severity}] Issue: {$this->issue->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->reportedByName} reported a **{$severity}** issue on **{$this->project->name}**.")
            ->line("**Issue:** {$this->issue->title}")
            ->line($this->issue->description ? "**Details:** " . \Illuminate\Support\Str::limit($this->issue->description, 200) : '')
            ->action('View Issue', $url)
            ->line('Please review and take action.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'issue_reported',
            'title' => "Issue reported: {$this->issue->title}",
            'body' => "{$this->reportedByName} reported an issue on {$this->project->name}",
            'data' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'issue_id' => $this->issue->id,
                'issue_title' => $this->issue->title,
                'severity' => $this->issue->severity ?? 'medium',
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
