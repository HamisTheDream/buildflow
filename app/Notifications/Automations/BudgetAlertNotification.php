<?php

namespace App\Notifications\Automations;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public int $threshold, // 80 or 100
        public float $spentCents,
        public float $budgetCents,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pct = $this->budgetCents > 0 ? round($this->spentCents / $this->budgetCents * 100) : 0;
        $spent = number_format($this->spentCents / 100, 2);
        $budget = number_format($this->budgetCents / 100, 2);
        $url = url("/app/projects/{$this->project->id}");

        $subject = $this->threshold >= 100
            ? "Budget exceeded: {$this->project->name}"
            : "Budget warning ({$pct}%): {$this->project->name}";

        return (new MailMessage)
            ->subject("BuildFlow: {$subject}")
            ->greeting("Hi {$notifiable->name},")
            ->line("**{$this->project->name}** has spent **₦{$spent}** of its **₦{$budget}** budget (**{$pct}%**).")
            ->line($this->threshold >= 100
                ? "Spend has crossed 100% of budget. Review costs urgently."
                : "Spend has crossed the 80% warning threshold.")
            ->action('View Project', $url)
            ->line('BuildFlow Automations');
    }
}
