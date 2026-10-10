<?php

namespace App\Notifications\Automations;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $stats keys: overdue_tasks, open_issues, unpaid_invoices_count,
     *                    unpaid_invoices_cents, budget_alerts, pending_leaves, new_leads
     */
    public function __construct(
        public Organization $org,
        public array $stats,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->stats;
        $unpaid = number_format(($s['unpaid_invoices_cents'] ?? 0) / 100, 2);
        $date = now()->format('d M Y');

        $mail = (new MailMessage)
            ->subject("BuildFlow daily briefing — {$this->org->name} ({$date})")
            ->greeting("Good morning, {$notifiable->name},")
            ->line("Here's what's happening at **{$this->org->name}** today:");

        $mail->line("• **{$s['overdue_tasks']}** overdue tasks");
        $mail->line("• **{$s['open_issues']}** open maintenance issues/tickets");
        $mail->line("• **{$s['unpaid_invoices_count']}** unpaid invoices (**₦{$unpaid}** outstanding)");
        $mail->line("• **{$s['budget_alerts']}** projects over 80% of budget");
        $mail->line("• **{$s['pending_leaves']}** pending leave requests");
        $mail->line("• **{$s['new_leads']}** new leads in the last 7 days");

        return $mail
            ->action('Open Dashboard', url('/app/dashboard'))
            ->line('BuildFlow Automations');
    }
}
