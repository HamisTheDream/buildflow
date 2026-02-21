<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'incurred_date' => 'required|date',
            'description' => 'required|string|max:255',
            'receipt' => [
                'nullable',
                'file',
                'mimes:jpeg,png,pdf',
                'max:10240',
                new \App\Rules\SafeFile,
            ],
            'reimbursable_to' => 'nullable|exists:users,id',
        ];
    }
}
