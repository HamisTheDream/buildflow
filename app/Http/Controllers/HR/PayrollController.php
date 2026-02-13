<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\PayrollRequest;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function create()
    {
        $orgId = auth()->user()->current_organization_id;

        // Fetch active employees
        $employees = Employee::where('organization_id', $orgId)
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'salary_amount_cents', 'payment_frequency')
            ->get()
            ->map(function ($employee) {
                // Calculate prorated or full salary base
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'base_salary' => $employee->salary_amount_cents / 100,
                    'payment_frequency' => $employee->payment_frequency,
                    // Default values for payroll run
                    'amount' => $employee->salary_amount_cents / 100,
                    'bonus' => 0,
                    'deductions' => 0,
                ];
            });

        return Inertia::render('HR/Payroll/Create', [
            'employees' => $employees,
        ]);
    }

    public function store(PayrollRequest $request)
    {
        $data = $request->validated();

        $totalCents = 0;
        $breakdown = [];

        foreach ($data['employees'] as $item) {
            $net = $item['amount'] + ($item['bonus'] ?? 0) - ($item['deductions'] ?? 0);
            $totalCents += ($net * 100);

            $breakdown[] = [
                'employee_id' => $item['id'],
                'base' => $item['amount'],
                'bonus' => $item['bonus'] ?? 0,
                'deductions' => $item['deductions'] ?? 0,
                'net' => $net,
            ];
        }

        Payroll::create([
            'organization_id' => auth()->user()->current_organization_id,
            'run_date' => $data['run_date'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_amount_cents' => (int) $totalCents,
            'status' => 'processed', // Auto-process for now
            'meta' => ['breakdown' => $breakdown],
        ]);

        return redirect()->route('hr.dashboard')->with('success', 'Payroll run processed successfully.');
    }

    public function show(Payroll $payroll)
    {
        return Inertia::render('HR/Payroll/Show', [
            'payroll' => $payroll,
            // Enrich meta breakdown with employee names
            'breakdown' => collect($payroll->meta['breakdown'])->map(function ($item) {
                $employee = Employee::find($item['employee_id']);
                return array_merge($item, ['employee_name' => $employee ? $employee->full_name : 'Unknown']);
            }),
        ]);
    }
}
