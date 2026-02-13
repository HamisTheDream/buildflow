<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\BudgetRequest;
use App\Models\Finance\Budget;
use App\Models\Finance\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function store(BudgetRequest $request)
    {
        $data = $request->validated();

        $data['total_amount_cents'] = (int) ($data['amount'] * 100);
        unset($data['amount']);

        Budget::create([
            'organization_id' => auth()->user()->current_organization_id,
            ...$data,
        ]);

        return redirect()->back()->with('success', 'Budget created successfully.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->back()->with('success', 'Budget deleted.');
    }
}
