<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SmallGroupRequest extends FormRequest
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
        $smallGroupId = $this->route('small_group')?->id ?? $this->route('smallGroup')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('small_groups', 'name')
                    ->ignore($smallGroupId)
                    ->where(function ($query) {
                        return $query->where('branch_id', $this->input('branch_id'));
                    }),
            ],
            'description' => 'nullable|string|max:1000',
            'branch_id' => 'sometimes|required|integer|exists:branches,id',
            'leader_id' => 'nullable|integer|exists:members,id',
            'meeting_day' => [
                'nullable',
                'string',
                Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            ],
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'status' => [
                'sometimes',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'branch_id' => 'branch',
            'leader_id' => 'group leader',
            'meeting_day' => 'meeting day',
            'meeting_time' => 'meeting time',
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
            'name.unique' => 'A small group with this name already exists in the selected branch.',
            'leader_id.exists' => 'The selected group leader must be a valid member.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'meeting_day.in' => 'Meeting day must be a valid day of the week.',
            'meeting_time.date_format' => 'Meeting time must be in HH:MM format (24-hour).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge(['status' => 'active']);
        }

        // For non-super admins, ensure they can only create groups in their branch
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch && !$this->has('branch_id')) {
                $this->merge(['branch_id' => $userBranch->id]);
            }
        }
    }
}
