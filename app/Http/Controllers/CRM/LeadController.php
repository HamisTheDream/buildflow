<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{


    public function create()
    {
        return Inertia::render('CRM/Leads/Create', [
            'users' => auth()->user()->currentOrganization->users()
                ->select('users.id', 'users.name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'required|string',
            'status' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        Lead::create([
            'organization_id' => auth()->user()->current_organization_id,
            ...$data,
        ]);

        return redirect()->route('crm.leads.index')->with('success', 'Lead created successfully.');
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'required|string',
        ]);

        $lead->update($data);

        return redirect()->back()->with('success', 'Lead status updated.');
    }
}
