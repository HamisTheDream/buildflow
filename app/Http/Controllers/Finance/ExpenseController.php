<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ExpenseRequest;
use App\Models\Finance\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function create()
    {
        $orgId = auth()->user()->current_organization_id;

        return Inertia::render('Finance/Expenses/Create', [
            'projects' => Project::where('organization_id', $orgId)->select('id', 'name')->get(),
            'users' => auth()->user()->currentOrganization->users()->select('users.id', 'users.name')->get(),
        ]);
    }

    public function store(ExpenseRequest $request)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts');
            $data['receipt_path'] = $path;
        }

        // Convert amount to cents
        $data['amount_cents'] = (int) ($data['amount'] * 100);
        unset($data['amount']);
        unset($data['receipt']);

        Expense::create([
            'organization_id' => auth()->user()->current_organization_id,
            'status' => 'pending',
            ...$data,
        ]);

        return redirect()->route('finance.dashboard')->with('success', 'Expense recorded successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,paid,rejected',
        ]);

        $expense->update($validated);

        return redirect()->back()->with('success', 'Expense status updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->back()->with('success', 'Expense removed.');
    }
}
