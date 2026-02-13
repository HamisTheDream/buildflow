<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'run_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employees' => 'required|array',
            'employees.*.id' => 'required|exists:employees,id',
            'employees.*.amount' => 'required|numeric|min:0',
            'employees.*.bonus' => 'nullable|numeric|min:0',
            'employees.*.deductions' => 'nullable|numeric|min:0',
        ];
    }
}
