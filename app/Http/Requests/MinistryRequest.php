<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MinistryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Basic authorization is handled in controller via policies
        // But we need to check if user can create/update ministry in the specified branch
        
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Super admins can create ministries in any branch
        if ($user->isSuperAdmin()) {
            return true;
        }

        // For branch pastors, check if they're trying to create/update in their own branch
        if ($user->isBranchPastor()) {
            $branchId = $this->input('branch_id');
            if ($branchId) {
                $userBranch = $user->getPrimaryBranch();
                return $userBranch && $userBranch->id == $branchId;
            }
        }

        // For updates, if no branch_id is being changed, allow other authorization to handle it
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $ministryId = $this->route('ministry')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ministries', 'name')
                    ->ignore($ministryId)
                    ->where('branch_id', $this->input('branch_id'))
                    ->whereNull('deleted_at'),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
            'leader_id' => [
                'nullable',
                'integer',
                'exists:members,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $member = \App\Models\Member::find($value);
                        if ($member && $member->member_status === 'visitor') {
                            $fail('Visitors cannot be assigned as ministry leaders.');
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
            'name.required' => 'Ministry name is required.',
            'name.unique' => 'A ministry with this name already exists in the selected branch.',
            'branch_id.required' => 'Branch selection is required.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'leader_id.exists' => 'Selected leader does not exist.',
            'status.required' => 'Ministry status is required.',
            'status.in' => 'Ministry status must be either active or inactive.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'branch_id' => 'branch',
            'leader_id' => 'ministry leader',
        ];
    }
} 