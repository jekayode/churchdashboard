<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GuestRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow guest registration
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Required fields for guest registration
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'consent_given' => 'required|accepted',
            'consent_given_at' => 'nullable|date',
            'consent_ip' => 'nullable|string|max:45',
            
            // Optional fields (can be filled during registration or later)
            'gender' => 'nullable|in:male,female,prefer-not-to-say',
            'preferred_call_time' => 'nullable|in:anytime,morning,afternoon,evening',
            'home_address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'age_group' => 'nullable|in:15-20,21-25,26-30,31-35,36-40,above-40',
            'marital_status' => 'nullable|in:single,in-relationship,engaged,married,separated,divorced',
            'prayer_request' => 'nullable|string|max:2000',
            'discovery_source' => 'nullable|in:social-media,word-of-mouth,billboard,email,website,promotional-material,radio-tv,outreach',
            'staying_intention' => 'nullable|in:yes-for-sure,visit-when-in-town,just-visiting,weighing-options',
            'closest_location' => 'nullable|string|max:255',
            'additional_info' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already registered.',
            'consent_given.required' => 'You must agree to the privacy policy to continue.',
            'consent_given.accepted' => 'You must agree to the privacy policy to continue.',
            'branch_id.exists' => 'Please select a valid branch.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Store consent timestamp and IP
        $this->merge([
            'consent_given_at' => now(),
            'consent_ip' => $this->ip(),
        ]);
    }
}
