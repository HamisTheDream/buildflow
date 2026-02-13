<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_number' => 'required|string|max:255',
            'type' => 'required|in:apartment,villa,shop,office,plot',
            'status' => 'required|in:available,reserved,sold',
            'size_sqm' => 'nullable|numeric|min:0',
            'rent_amount' => 'nullable|numeric|min:0', // Input as dollars/float
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
        ];
    }
}
