<?php

namespace App\Notifications\Automations;

use App\Models\Finance\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Stage: 'upcoming' (7 days before), 'due' (due date),
     * 'overdue_7', 'overdue_14', 'overdue_30'.
     */
    public function __construct(
        public Invoice $invoice,
        public string $stage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $org = $this->invoice->organization;
        $amount = number_format($this->invoice->total_amount_cents / 100, 2);
        $due = $this->invoice->due_date?->format('d M Y') ?? '—';

        [$subject, $line] = match ($this->stage) {
            'upcoming' => [
                "Payment due soon — Invoice {$this->invoice->number}",
                "This is a friendly reminder that invoice **{$this->invoice->number}** for **₦{$amount}** is due on **{$due}**.",
            ],
            'due' => [
                "Payment due today — Invoice {$this->invoice->number}",
                "Invoice **{$this->invoice->number}** for **₦{$amount}** is due **today** ({$due}).",
            ],
            'overdue_7' => [
                "Overdue: Invoice {$this->invoice->number}",
                "Invoice **{$this->invoice->number}** for **₦{$amount}** was due on **{$due}** and is now **7 days overdue**.",
            ],
            'overdue_14' => [
                "Second notice: Invoice {$this->invoice->number} overdue",
                "Invoice **{$this->invoice->number}** for **₦{$amount}** is now **14 days overdue** (was due {$due}).",
            ],
            default => [
                "Final notice: Invoice {$this->invoice->number} 30 days overdue",
                "Invoice **{$this->invoice->number}** for **₦{$amount}** is now **30 days overdue** (was due {$due}). Please settle it immediately to avoid further action.",
            ],
        };

        return (new MailMessage)
            ->subject("{$org->name}: {$subject}")
            ->greeting("Hello,")
            ->line($line)
            ->line("If you have already paid, please disregard this message.")
            ->line("— {$org->name} (via BuildFlow)");
    }
}
