<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'status' => ['required', 'in:todo,doing,done,blocked'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'project_unit_id' => ['nullable', 'integer', 'exists:project_units,id'],
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['title'] = ['sometimes', 'required', 'string', 'max:255'];
            $rules['priority'] = ['sometimes', 'required', 'in:low,normal,high,urgent'];
            $rules['status'] = ['sometimes', 'required', 'in:todo,doing,done,blocked'];
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
            'title.required' => 'Please enter a task title.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'status.required' => 'Please select a task status.',
            'status.in' => 'Please select a valid task status.',
            'priority.required' => 'Please select a task priority.',
            'priority.in' => 'Please select a valid priority level.',
            'due_date.date' => 'Please enter a valid due date.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'project_unit_id.exists' => 'The selected unit does not exist.',
        ];
    }
}
