<?php

declare(strict_types=1);

namespace App\Http\Requests\Me;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Fields a member may change on their own profile.
 *
 * Deliberately excludes branch, member_status, growth_level and teci_status —
 * those are leadership-managed and must not be self-editable.
 */
final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->member !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $memberId = $this->user()->member->id;

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'surname' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:members,email,'.$memberId],
            'gender' => ['sometimes', 'nullable', 'in:male,female,prefer-not-to-say'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'marital_status' => ['sometimes', 'nullable', 'in:single,in-relationship,engaged,married,separated,divorced'],
            'occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'home_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'nearest_bus_stop' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
