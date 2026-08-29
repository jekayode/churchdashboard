<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreWorkforceDriveSignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public Workforce Drive sign-up.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Unique across the whole drive — one person joins one team only.
            'email' => 'required|email|max:255|unique:workforce_drive_signups,email',
            'phone' => 'required|string|max:20',
            'workforce_drive_team_id' => 'required|exists:workforce_drive_teams,id',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email has already joined a team.',
            'workforce_drive_team_id.required' => 'Please choose a team to join.',
            'workforce_drive_team_id.exists' => 'Please choose a valid team.',
        ];
    }

    /**
     * Human-friendly attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'workforce_drive_team_id' => 'team',
        ];
    }
}
