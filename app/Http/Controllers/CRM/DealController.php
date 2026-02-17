<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\PropertyUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DealController extends Controller
{


    public function create()
    {
        return Inertia::render('CRM/Deals/Create', [
            'leads' => Lead::where('organization_id', auth()->user()->current_organization_id)
                ->select('id', 'first_name', 'last_name')
                ->get()
                ->map(fn($l) => ['id' => $l->id, 'name' => $l->first_name . ' ' . $l->last_name]),
            'units' => PropertyUnit::whereHas('property', function ($q) {
                $q->where('organization_id', auth()->user()->current_organization_id);
            })
                ->where('status', 'available')
                ->with('property:id,name')
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->property->name . ' - ' . $u->unit_number
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'lead_id' => 'nullable|exists:leads,id',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'amount' => 'required|numeric|min:0',
            'stage' => 'required|string',
            'expected_close_date' => 'nullable|date',
        ]);

        // Convert amount to cents
        $data['amount_cents'] = (int) ($data['amount'] * 100);
        unset($data['amount']);

        Deal::create([
            'organization_id' => auth()->user()->current_organization_id,
            ...$data,
        ]);

        return redirect()->route('crm.dashboard', ['tab' => 'sales'])->with('success', 'Deal created successfully.');
    }

    public function update(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'stage' => 'required|string',
        ]);

        $deal->update($data);

        return redirect()->back()->with('success', 'Deal stage updated.');
    }
}
