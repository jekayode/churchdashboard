<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class AddGuestFollowUpRequest extends FormRequest
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
            'follow_up_type' => 'required|string|max:100',
            'contact_date' => 'required|date',
            'contact_status' => 'required|in:pending,completed,rescheduled,cancelled',
            'notes' => 'required|string|max:2000',
            'next_follow_up_date' => 'nullable|date|after:contact_date',
            'outcome' => 'nullable|string|max:500',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'follow_up_type.required' => 'Please specify the type of follow-up.',
            'contact_date.required' => 'Please provide the contact date.',
            'contact_date.date' => 'The contact date must be a valid date.',
            'contact_status.required' => 'Please select the contact status.',
            'contact_status.in' => 'The selected contact status is invalid.',
            'notes.required' => 'Please provide notes about the follow-up.',
            'notes.max' => 'The notes cannot exceed 2000 characters.',
            'next_follow_up_date.date' => 'The next follow-up date must be a valid date.',
            'next_follow_up_date.after' => 'The next follow-up date must be after the contact date.',
            'outcome.max' => 'The outcome cannot exceed 500 characters.',
            'assigned_to.exists' => 'The assigned user does not exist.',
        ];
    }
}

