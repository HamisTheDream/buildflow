<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueRequest extends FormRequest
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
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:open,in_progress,blocked,resolved,closed'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'category' => ['required', 'in:general,quality,safety,material,labor,client,finance,scope,other'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'project_unit_id' => ['nullable', 'integer', 'exists:project_units,id'],
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['title'] = ['sometimes', 'required', 'string', 'max:255'];
            $rules['severity'] = ['sometimes', 'required', 'in:low,medium,high,critical'];
            $rules['category'] = ['sometimes', 'required', 'in:general,quality,safety,material,labor,client,finance,scope,other'];
            $rules['status'] = ['sometimes', 'required', 'in:open,in_progress,blocked,resolved,closed'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify assignee is a project member
            if ($this->assigned_to) {
                $project = $this->route('project');
                if ($project && !$project->members()->where('users.id', $this->assigned_to)->exists()) {
                    $validator->errors()->add('assigned_to', 'Selected user is not a member of this project.');
                }
            }

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
            'title.required' => 'Please enter an issue title.',
            'title.max' => 'Issue title cannot exceed 255 characters.',
            'status.required' => 'Please select an issue status.',
            'status.in' => 'Please select a valid status.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Please select a valid priority level.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'project_unit_id.exists' => 'The selected unit does not exist.',
        ];
    }
}
