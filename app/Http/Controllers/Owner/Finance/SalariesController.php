<?php

namespace App\Http\Controllers\Owner\Finance;

use App\Http\Controllers\Controller;
use App\Models\OwnerSalary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalariesController extends Controller
{
    public function index()
    {
        $salaries = OwnerSalary::latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Finance/Salaries', [
            'salaries' => $salaries,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,weekly',
            'next_payment_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        OwnerSalary::create([
            'employee_name' => $validated['employee_name'],
            'role' => $validated['role'],
            'amount_cents' => (int) ($validated['amount'] * 100),
            'frequency' => $validated['frequency'],
            'next_payment_date' => $validated['next_payment_date'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Salary entry created successfully.');
    }

    public function update(Request $request, OwnerSalary $salary)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,weekly',
            'next_payment_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $salary->update([
            'employee_name' => $validated['employee_name'],
            'role' => $validated['role'],
            'amount_cents' => (int) ($validated['amount'] * 100),
            'frequency' => $validated['frequency'],
            'next_payment_date' => $validated['next_payment_date'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Salary entry updated successfully.');
    }

    public function destroy(OwnerSalary $salary)
    {
        $salary->delete();
        return redirect()->back()->with('success', 'Salary entry deleted successfully.');
    }
}
