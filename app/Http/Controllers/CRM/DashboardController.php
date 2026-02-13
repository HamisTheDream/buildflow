<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Property;
use App\Models\CRM\PropertyUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orgId = Auth::user()->current_organization_id;
        $tab = $request->get('tab', 'overview');

        // Stats for overview
        $totalProperties = Property::where('organization_id', $orgId)->count();
        $totalUnits = PropertyUnit::whereHas('property', fn($q) => $q->where('organization_id', $orgId))->count();
        $availableUnits = PropertyUnit::whereHas('property', fn($q) => $q->where('organization_id', $orgId))
            ->where('status', 'available')->count();
        $soldUnits = PropertyUnit::whereHas('property', fn($q) => $q->where('organization_id', $orgId))
            ->where('status', 'sold')->count();
        $reservedUnits = PropertyUnit::whereHas('property', fn($q) => $q->where('organization_id', $orgId))
            ->where('status', 'reserved')->count();
        $totalLeads = Lead::where('organization_id', $orgId)->count();
        $pipelineValue = Deal::where('organization_id', $orgId)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->sum('amount_cents');
        $totalRevenue = Deal::where('organization_id', $orgId)
            ->where('stage', 'closed_won')
            ->sum('amount_cents');

        // Properties with units (Inventory)
        $properties = Property::where('organization_id', $orgId)
            ->withCount('units')
            ->with(['units' => function ($q) {
                $q->orderBy('unit_number');
            }, 'project:id,name'])
            ->latest()
            ->get();

        // Leads
        $leads = Lead::where('organization_id', $orgId)
            ->with('assignedTo:id,name')
            ->latest()
            ->get();

        // Deals grouped by stage
        $deals = Deal::where('organization_id', $orgId)
            ->with(['lead:id,first_name,last_name', 'propertyUnit:id,unit_number'])
            ->latest()
            ->get();

        // Recent activity (last 5 leads + deals)
        $recentLeads = Lead::where('organization_id', $orgId)
            ->latest()->take(5)->get();
        $recentDeals = Deal::where('organization_id', $orgId)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->with('lead:id,first_name,last_name')
            ->latest()->take(5)->get();

        return Inertia::render('CRM/Dashboard', [
            'tab' => $tab,
            'stats' => [
                'totalProperties' => $totalProperties,
                'totalUnits' => $totalUnits,
                'availableUnits' => $availableUnits,
                'soldUnits' => $soldUnits,
                'reservedUnits' => $reservedUnits,
                'totalLeads' => $totalLeads,
                'pipelineValue' => $pipelineValue,
                'totalRevenue' => $totalRevenue,
            ],
            'properties' => $properties,
            'leads' => $leads,
            'deals' => $deals,
            'recentLeads' => $recentLeads,
            'recentDeals' => $recentDeals,
            'stages' => [
                'prospecting' => 'Inquiry',
                'qualification' => 'Site Visit',
                'proposal' => 'Offer Made',
                'negotiation' => 'Negotiation',
                'closed_won' => 'Sold',
                'closed_lost' => 'Lost',
            ],
        ]);
    }
}
