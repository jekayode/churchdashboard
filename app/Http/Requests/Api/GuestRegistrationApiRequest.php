<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sign-up from the app.
 *
 * The required fields mirror the public web form, because they feed the same
 * service and produce the same member record — a sign-up that skipped the phone
 * number or the branch would leave a member the church cannot follow up.
 */
final class GuestRegistrationApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
            'password' => ['required', 'string', 'min:8'],
            'consent_given' => ['required', 'accepted'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent_given.required' => 'You must agree to the privacy policy to continue.',
            'consent_given.accepted' => 'You must agree to the privacy policy to continue.',
            'email.unique' => 'This email is already registered. Try signing in instead.',
            'password.min' => 'Please use at least 8 characters.',
        ];
    }

    /**
     * The consent record is written here, from the server's own clock and the
     * request's own address. Taking either from the client would make it a
     * field an app could set to anything, which is not a consent record.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'consent_given_at' => now(),
            'consent_ip' => $this->ip(),
        ]);
    }
}
