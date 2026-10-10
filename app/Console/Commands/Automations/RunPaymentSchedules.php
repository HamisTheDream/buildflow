<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Finance\PaymentSchedule;
use App\Models\Finance\Invoice;
use App\Models\Organization;
use Illuminate\Console\Command;

class RunPaymentSchedules extends Command
{
    protected $signature = 'automations:payment-schedules';
    protected $description = 'Generate invoices from due payment schedules (installment & recurring billing)';

    public function handle(): int
    {
        $count = 0;

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'payment_schedules')) {
                continue;
            }

            $schedules = PaymentSchedule::where('organization_id', $org->id)
                ->where('status', 'active')
                ->with(['clientLead', 'clientUser'])
                ->get()
                ->filter(fn ($s) => $s->isDue());

            foreach ($schedules as $schedule) {
                $invoice = Invoice::create([
                    'organization_id' => $org->id,
                    'client_lead_id' => $schedule->client_lead_id,
                    'client_user_id' => $schedule->client_user_id,
                    'project_id' => $schedule->project_id,
                    'number' => 'INV-' . date('Y') . '-' . str_pad(
                        Invoice::where('organization_id', $org->id)->count() + 1, 3, '0', STR_PAD_LEFT
                    ),
                    'issue_date' => now()->toDateString(),
                    'due_date' => now()->addDays(14)->toDateString(),
                    'total_amount_cents' => $schedule->amount_cents,
                    'status' => 'sent',
                    'notes' => ($schedule->meta['description'] ?? $schedule->name)
                        . " — installment " . ($schedule->runs_completed + 1)
                        . ($schedule->max_runs ? " of {$schedule->max_runs}" : ''),
                    'meta' => ['payment_schedule_id' => $schedule->id],
                ]);

                $schedule->advance();

                AutomationLog::record($org->id, 'payment_schedules', 'invoice_generated', 'invoice', $invoice->id, [
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                    'amount_cents' => $schedule->amount_cents,
                ]);

                $count++;
            }
        }

        $this->info("Generated {$count} invoices from payment schedules.");
        return self::SUCCESS;
    }
}
