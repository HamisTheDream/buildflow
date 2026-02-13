<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\PropertyUnitRequest;
use App\Models\CRM\Property;
use App\Models\CRM\PropertyUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyUnitController extends Controller
{
    public function create(Property $property)
    {
        return Inertia::render('CRM/Units/Create', [
            'property' => $property,
        ]);
    }

    public function store(PropertyUnitRequest $request, Property $property)
    {
        $data = $request->validated();

        // Convert rent_amount to cents
        $data['rent_amount_cents'] = isset($data['rent_amount']) ? (int) ($data['rent_amount'] * 100) : 0;
        unset($data['rent_amount']);

        $property->units()->create($data);

        return redirect()->route('crm.properties.show', $property->id)
            ->with('success', 'Unit added successfully.');
    }

    public function destroy(Property $property, PropertyUnit $unit)
    {
        $unit->delete();

        return redirect()->back()->with('success', 'Unit remove successfully.');
    }
}
