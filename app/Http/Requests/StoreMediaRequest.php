<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
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
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            return [
                'caption' => ['nullable', 'string', 'max:500'],
            ];
        }

        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:20480', // 20MB max
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,csv,txt',
                new \App\Rules\SafeFile,
            ],
            'caption' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Please select at least one file to upload.',
            'files.array' => 'Invalid file format.',
            'files.*.file' => 'The upload must be a valid file.',
            'files.*.max' => 'File size cannot exceed 20MB.',
            'files.*.mimes' => 'File type not allowed. Please upload images, videos, or documents.',
            'caption.max' => 'Caption cannot exceed 500 characters.',
        ];
    }
}
