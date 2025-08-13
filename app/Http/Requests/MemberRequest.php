<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $memberId = $this->route('member')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('members', 'user_id')->ignore($memberId),
            ],
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('members', 'email')->ignore($memberId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'anniversary' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'gender' => [
                'nullable',
                Rule::in(['male', 'female']),
            ],
            'marital_status' => [
                'nullable',
                Rule::in(['single', 'married', 'divorced', 'separated', 'widowed', 'in_a_relationship', 'engaged']),
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:255',
            ],
            'nearest_bus_stop' => [
                'nullable',
                'string',
                'max:255',
            ],
            'date_joined' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'date_attended_membership_class' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'teci_status' => [
                'nullable',
                Rule::in([
                    'not_started',
                    '100_level',
                    '200_level',
                    '300_level',
                    '400_level',
                    '500_level',
                    'graduated',
                    'paused'
                ]),
            ],
            'growth_level' => [
                'nullable',
                Rule::in(['core', 'pastor', 'growing', 'new_believer']),
            ],
            'leadership_trainings' => [
                'nullable',
                'array',
            ],
            'leadership_trainings.*' => [
                'string',
                Rule::in(['ELP', 'MLCC', 'MLCP Basic', 'MLCP Advanced']),
            ],
            'member_status' => [
                'nullable',
                Rule::in(['visitor', 'member', 'volunteer', 'leader', 'minister']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Member name is required.',
            'name.min' => 'Member name must be at least 2 characters.',
            'branch_id.required' => 'Branch selection is required.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered to another member.',
            'phone.regex' => 'Please provide a valid phone number.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'date_of_birth.after' => 'Date of birth must be after 1900.',
            'anniversary.before_or_equal' => 'Anniversary date cannot be in the future.',
            'gender.in' => 'Gender must be either male or female.',
            'marital_status.in' => 'Please select a valid marital status.',
            'date_joined.before_or_equal' => 'Date joined cannot be in the future.',
            'date_attended_membership_class.before_or_equal' => 'Membership class date cannot be in the future.',
            'teci_status.in' => 'Please select a valid TECI status.',
            'growth_level.in' => 'Please select a valid growth level.',
            'leadership_trainings.*.in' => 'Invalid leadership training selected.',
            'member_status.in' => 'Please select a valid member status.',
            'user_id.unique' => 'This user is already linked to another member.',
            'user_id.exists' => 'Selected user does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user account',
            'branch_id' => 'branch',
            'date_of_birth' => 'date of birth',
            'date_joined' => 'date joined',
            'date_attended_membership_class' => 'membership class date',
            'teci_status' => 'TECI status',
            'growth_level' => 'growth level',
            'leadership_trainings' => 'leadership trainings',
            'member_status' => 'member status',
            'nearest_bus_stop' => 'nearest bus stop',
        ];
    }
} 