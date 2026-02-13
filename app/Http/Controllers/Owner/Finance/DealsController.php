<?php

namespace App\Http\Controllers\Owner\Finance;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OwnerDeal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DealsController extends Controller
{
    public function index()
    {
        $deals = OwnerDeal::with('organization:id,name')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Finance/Deals', [
            'deals' => $deals,
            'organizations' => Organization::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value_amount' => 'required|numeric|min:0',
            'status' => 'required|in:open,won,lost',
            'organization_id' => 'nullable|exists:organizations,id',
            'details' => 'nullable|string',
            'close_date' => 'nullable|date',
        ]);

        OwnerDeal::create([
            'title' => $validated['title'],
            'value_cents' => (int) ($validated['value_amount'] * 100),
            'status' => $validated['status'],
            'organization_id' => $validated['organization_id'],
            'details' => $validated['details'],
            'close_date' => $validated['close_date'],
        ]);

        return redirect()->back()->with('success', 'Deal created successfully.');
    }

    public function update(Request $request, OwnerDeal $deal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value_amount' => 'required|numeric|min:0',
            'status' => 'required|in:open,won,lost',
            'organization_id' => 'nullable|exists:organizations,id',
            'details' => 'nullable|string',
            'close_date' => 'nullable|date',
        ]);

        $deal->update([
            'title' => $validated['title'],
            'value_cents' => (int) ($validated['value_amount'] * 100),
            'status' => $validated['status'],
            'organization_id' => $validated['organization_id'],
            'details' => $validated['details'],
            'close_date' => $validated['close_date'],
        ]);

        return redirect()->back()->with('success', 'Deal updated successfully.');
    }

    public function destroy(OwnerDeal $deal)
    {
        $deal->delete();
        return redirect()->back()->with('success', 'Deal deleted successfully.');
    }
}
