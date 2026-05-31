<?php

declare(strict_types=1);

namespace App\Http\Requests\Builders;

use App\Enums\BuilderIndustry;
use App\Enums\BusinessChallenge;
use App\Enums\BusinessStage;
use App\Enums\CacStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBuilderRegistrationRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_description' => ['required', 'string', 'max:5000'],
            'business_stage' => ['required', 'string', Rule::in(BusinessStage::values())],
            'industry' => ['required', 'string', Rule::in(BuilderIndustry::values())],
            'industry_other' => ['nullable', 'required_if:industry,other', 'string', 'max:255'],
            'biggest_challenge' => ['required', 'string', Rule::in(BusinessChallenge::values())],
            'success_vision' => ['required', 'string', 'max:5000'],
            'cac_status' => ['required', 'string', Rule::in(CacStatus::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'industry_other.required_if' => 'Please specify your industry.',
        ];
    }
}
