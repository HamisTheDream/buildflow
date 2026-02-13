<?php

namespace App\Http\Controllers\Owner\Finance;

use App\Http\Controllers\Controller;
use App\Models\OwnerExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpensesController extends Controller
{
    public function index()
    {
        $expenses = OwnerExpense::latest('date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Finance/Expenses', [
            'expenses' => $expenses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);

        OwnerExpense::create([
            'title' => $validated['title'],
            'amount_cents' => (int) ($validated['amount'] * 100),
            'date' => $validated['date'],
            'category' => $validated['category'],
            'details' => $validated['details'],
        ]);

        return redirect()->back()->with('success', 'Expense logged successfully.');
    }

    public function update(Request $request, OwnerExpense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);

        $expense->update([
            'title' => $validated['title'],
            'amount_cents' => (int) ($validated['amount'] * 100),
            'date' => $validated['date'],
            'category' => $validated['category'],
            'details' => $validated['details'],
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(OwnerExpense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully.');
    }
}
