<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:residential,commercial,mixed,land',
            'status' => 'required|in:planning,construction,ready,sold_out',
            'address' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'total_units' => 'integer|min:0',
        ];
    }
}
