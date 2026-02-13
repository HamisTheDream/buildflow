<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'status' => ['nullable', 'in:pending,approved,paid,rejected'],
            'cost_date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,transfer,card,cheque,other'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'project_unit_id' => ['nullable', 'integer', 'exists:project_units,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify unit belongs to project
            if ($this->project_unit_id) {
                $project = $this->route('project');
                if ($project && !$project->units()->where('id', $this->project_unit_id)->exists()) {
                    $validator->errors()->add('project_unit_id', 'Invalid unit.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Please enter a cost description.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'category.required' => 'Please select a category.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be greater than zero.',
            'status.in' => 'Please select a valid status.',
            'cost_date.date' => 'Please enter a valid date.',
            'payment_method.in' => 'Please select a valid payment method.',
            'project_unit_id.exists' => 'The selected unit does not exist.',
        ];
    }
}
