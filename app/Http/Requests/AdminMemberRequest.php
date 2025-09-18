<?php

declare(strict_types=1);

namespace App\Http\Requests;

class AdminMemberRequest extends BaseMemberRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        return $user->hasRole('super_admin') || $user->hasRole('branch_pastor');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = $this->getCommonRules();
        
        // For admin, email uniqueness depends on whether we're creating or updating
        if ($this->isMethod('POST')) {
            $rules['email'] = 'required|email|unique:users,email';
        } else {
            $rules['email'] = 'required|email|unique:users,email,' . $this->route('member')?->user_id;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge($this->getCommonMessages(), [
            'email.unique' => 'This email address is already registered.',
        ]);
    }
}
