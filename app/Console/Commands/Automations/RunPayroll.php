<?php

namespace App\Console\Commands\Automations;

use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Models\Organization;
use Illuminate\Console\Command;

class RunPayroll extends Command
{
    protected $signature = 'automations:run-payroll';
    protected $description = 'Generate a draft monthly payroll from active staff on the 1st of each month';

    public function handle(): int
    {
        $created = 0;
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        foreach (Organization::all() as $org) {
            if (! AutomationSetting::isEnabled($org->id, 'auto_payroll')) {
                continue;
            }

            // Never create two payrolls for the same month
            $exists = Payroll::where('organization_id', $org->id)
                ->whereDate('start_date', $monthStart->toDateString())
                ->exists();
            if ($exists) {
                continue;
            }

            $staff = Employee::where('organization_id', $org->id)
                ->where('status', 'active')
                ->get();

            if ($staff->isEmpty()) {
                continue;
            }

            $breakdown = [];
            $total = 0;
            foreach ($staff as $employee) {
                // salary_amount_cents is the monthly base for monthly-frequency staff
                $amount = $employee->payment_frequency === 'monthly'
                    ? (int) $employee->salary_amount_cents
                    : (int) round($employee->salary_amount_cents / 2); // bi-weekly → ~2 per month
                $breakdown[] = [
                    'employee_id' => $employee->id,
                    'name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'job_title' => $employee->job_title,
                    'amount_cents' => $amount,
                ];
                $total += $amount;
            }

            $payroll = Payroll::create([
                'organization_id' => $org->id,
                'run_date' => now()->toDateString(),
                'start_date' => $monthStart->toDateString(),
                'end_date' => $monthEnd->toDateString(),
                'total_amount_cents' => $total,
                'status' => 'draft',
                'meta' => [
                    'breakdown' => $breakdown,
                    'generated_by' => 'automation:auto_payroll',
                ],
            ]);

            AutomationLog::record($org->id, 'auto_payroll', 'payroll_generated', 'payroll', $payroll->id, [
                'month' => $monthStart->format('Y-m'),
                'staff_count' => $staff->count(),
                'total_cents' => $total,
            ]);

            $created++;
        }

        $this->info("Generated {$created} payroll runs.");
        return self::SUCCESS;
    }
}
