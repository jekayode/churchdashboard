<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')->ignore($branchId)->whereNull('deleted_at'),
            ],
            'logo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'venue' => [
                'required',
                'string',
                'max:255',
            ],
            'service_time' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('branches', 'email')->ignore($branchId)->whereNull('deleted_at'),
            ],
            'map_embed_code' => [
                'nullable',
                'string',
            ],
            'pastor_id' => [
                'nullable',
                'exists:users,id',
            ],
            'status' => [
                'required',
                'in:active,inactive,suspended',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'branch name',
            'venue' => 'venue location',
            'service_time' => 'service time',
            'phone' => 'phone number',
            'email' => 'email address',
            'map_embed_code' => 'map embed code',
            'pastor_id' => 'pastor',
            'status' => 'branch status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A branch with this name already exists.',
            'email.unique' => 'A branch with this email already exists.',
            'phone.regex' => 'Please enter a valid phone number.',
            'pastor_id.exists' => 'The selected pastor does not exist.',
            'status.in' => 'The status must be active, inactive, or suspended.',
        ];
    }
} 