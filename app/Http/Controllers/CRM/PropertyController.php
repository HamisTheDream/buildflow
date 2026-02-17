<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\PropertyRequest;
use App\Models\CRM\Property;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{


    public function create()
    {
        return Inertia::render('CRM/Properties/Create', [
            'projects' => Project::where('organization_id', auth()->user()->current_organization_id)
                ->select('id', 'name')
                ->get(),
        ]);
    }

    public function store(PropertyRequest $request)
    {
        Property::create([
            'organization_id' => auth()->user()->current_organization_id,
            ...$request->validated(),
        ]);

        return redirect()->route('crm.dashboard', ['tab' => 'inventory'])->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        $property->load(['units', 'project']);

        return Inertia::render('CRM/Properties/Show', [
            'property' => $property,
        ]);
    }
}
