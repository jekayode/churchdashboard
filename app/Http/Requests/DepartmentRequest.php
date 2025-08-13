<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Super admins can create departments anywhere
        if ($user->isSuperAdmin()) {
            return true;
        }

        // For creating new departments, check if user can access the ministry
        if ($this->isMethod('POST') && $this->has('ministry_id')) {
            $ministry = \App\Models\Ministry::find($this->input('ministry_id'));
            
            if (!$ministry) {
                return false;
            }

            // Branch pastors can create departments in ministries within their branch
            if ($user->isBranchPastor()) {
                $userBranch = $user->getPrimaryBranch();
                return $userBranch && $userBranch->id === $ministry->branch_id;
            }

            // Ministry leaders can only create departments in their own ministry
            if ($user->isMinistryLeader() && $user->member) {
                return $user->member->id === $ministry->leader_id;
            }
        }

        // For updating, use the existing policy authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->ignore($departmentId)
                    ->where('ministry_id', $this->input('ministry_id'))
                    ->whereNull('deleted_at'),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'ministry_id' => [
                'required',
                'integer',
                'exists:ministries,id',
            ],
            'leader_id' => [
                'nullable',
                'integer',
                'exists:members,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $member = \App\Models\Member::find($value);
                        if ($member && $member->member_status === 'visitor') {
                            $fail('Visitors cannot be assigned as department leaders.');
                        }
                    }
                },
            ],
            'status' => [
                'required',
                'string',
                'in:active,inactive',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Department name is required.',
            'name.unique' => 'A department with this name already exists in the selected ministry.',
            'ministry_id.required' => 'Ministry selection is required.',
            'ministry_id.exists' => 'Selected ministry does not exist.',
            'leader_id.exists' => 'Selected leader does not exist.',
            'status.required' => 'Department status is required.',
            'status.in' => 'Department status must be either active or inactive.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'ministry_id' => 'ministry',
            'leader_id' => 'department leader',
        ];
    }
} 