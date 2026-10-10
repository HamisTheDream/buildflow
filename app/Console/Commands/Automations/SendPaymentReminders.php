<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Finance\Invoice;
use App\Models\Organization;
use App\Notifications\Automations\PaymentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPaymentReminders extends Command
{
    protected $signature = 'automations:payment-reminders';
    protected $description = 'Send staged payment reminders for invoices (7d before, due date, 7/14/30d overdue)';

    public function handle(): int
    {
        $sent = 0;
        $today = now()->startOfDay();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'payment_reminders')) {
                continue;
            }

            $invoices = Invoice::where('organization_id', $org->id)
                ->whereIn('status', ['sent', 'overdue'])
                ->whereNotNull('due_date')
                ->with(['organization', 'clientLead', 'clientUser'])
                ->get();

            foreach ($invoices as $invoice) {
                $due = $invoice->due_date->startOfDay();
                $diff = $today->diffInDays($due, false); // negative = overdue

                $stage = match (true) {
                    $diff === 7 => 'upcoming',
                    $diff === 0 => 'due',
                    $diff === -7 => 'overdue_7',
                    $diff === -14 => 'overdue_14',
                    $diff === -30 => 'overdue_30',
                    default => null,
                };

                if ($stage === null) {
                    continue;
                }

                // Mark overdue once it passes the due date
                if ($diff < 0 && $invoice->status !== 'overdue') {
                    $invoice->update(['status' => 'overdue']);
                }

                $action = "reminder_{$stage}_sent";
                if (AutomationLog::alreadyDid($org->id, 'payment_reminders', $action, 'invoice', $invoice->id)) {
                    continue;
                }

                $recipient = $this->resolveRecipient($invoice, $org);
                if (! $recipient) {
                    continue;
                }

                Notification::route('mail', $recipient['email'])
                    ->notify(new PaymentReminderNotification($invoice, $stage));

                AutomationLog::record($org->id, 'payment_reminders', $action, 'invoice', $invoice->id, [
                    'stage' => $stage,
                    'recipient' => $recipient['email'],
                    'amount_cents' => $invoice->total_amount_cents,
                ]);

                $sent++;
            }
        }

        $this->info("Sent {$sent} payment reminders.");
        return self::SUCCESS;
    }

    private function resolveRecipient(Invoice $invoice, Organization $org): ?array
    {
        $email = $invoice->clientLead?->email ?? $invoice->clientUser?->email;
        if ($email) {
            return ['email' => $email];
        }

        // No client email on file — route to the org owner so they can chase manually.
        $owner = $org->users()->wherePivot('role', 'owner')->first();
        return $owner ? ['email' => $owner->email] : null;
    }
}
