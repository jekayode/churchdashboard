<?php

declare(strict_types=1);

namespace App\Http\Requests;

class ProfileCompletionRequest extends BaseMemberRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // For profile completion, only optional fields are validated
        $rules = $this->getCommonRules();
        
        // Remove required fields since they're already set
        unset($rules['first_name'], $rules['surname'], $rules['email'], $rules['phone'], $rules['branch_id']);

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return $this->getCommonMessages();
    }
}




