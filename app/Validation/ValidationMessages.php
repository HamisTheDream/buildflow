<?php

namespace App\Validation;

/**
 * Humanized validation messages for BuildFlow.
 * Load this in AppServiceProvider boot() or use in Form Requests.
 */
class ValidationMessages
{
    public static function all(): array
    {
        return [
            // General
            'required' => 'Please enter :attribute.',
            'string' => ':Attribute must be text.',
            'numeric' => ':Attribute must be a number.',
            'integer' => ':Attribute must be a whole number.',
            'email' => 'This doesn\'t look like a valid email address.',
            'unique' => 'This :attribute is already taken.',
            'confirmed' => 'The confirmation doesn\'t match.',
            'min.string' => ':Attribute must be at least :min characters.',
            'max.string' => ':Attribute can\'t be longer than :max characters.',
            'max.file' => ':Attribute can\'t be larger than :max KB.',
            'mimes' => ':Attribute must be a :values file.',
            'image' => ':Attribute must be an image.',
            'date' => ':Attribute must be a valid date.',
            'after' => ':Attribute must be after :date.',
            'before' => ':Attribute must be before :date.',
            'in' => 'Please select a valid :attribute.',
            'exists' => 'The selected :attribute doesn\'t exist.',
            'url' => 'Please enter a valid URL.',
            'boolean' => ':Attribute must be true or false.',
            'array' => ':Attribute must be a list.',
            'file' => ':Attribute must be a file.',
            'uploaded' => ':Attribute failed to upload. Please try again.',

            // Custom project-specific messages
            'name.required' => 'Please enter a project name.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'Amount must be a number.',
            'category.in' => 'Please select a valid category.',
            'priority.in' => 'Please select a valid priority.',
            'status.in' => 'Please select a valid status.',
            'end_date.after' => 'End date must be after the start date.',
        ];
    }

    /**
     * Custom attribute names for friendlier messages.
     */
    public static function attributes(): array
    {
        return [
            'client_name' => 'client name',
            'client_phone' => 'client phone',
            'client_email' => 'client email',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'is_approved' => 'approval status',
            'created_by_user_id' => 'creator',
            'organization_id' => 'organization',
        ];
    }
}
