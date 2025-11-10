<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class UpdateGuestStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->route('member');
        return Gate::allows('viewGuest', [$member]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_status' => 'required|in:member,volunteer,leader,minister',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'new_status.required' => 'Please select a new status for the guest.',
            'new_status.in' => 'The selected status is invalid.',
            'reason.max' => 'The reason cannot exceed 500 characters.',
            'notes.max' => 'The notes cannot exceed 2000 characters.',
        ];
    }
}

