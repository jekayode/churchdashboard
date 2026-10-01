<?php

declare(strict_types=1);

namespace App\Http\Requests\Pastor;

use App\Enums\QuizTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class QuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The controller authorizes against the policy.
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            // 5s is about the floor for reading a question and choosing; 120s is
            // longer than any congregation will sit through.
            'seconds_per_question' => ['required', 'integer', 'min:5', 'max:120'],
            'reveal_seconds' => ['required', 'integer', 'min:2', 'max:30'],
            'base_points' => ['required', 'integer', 'min:100', 'max:5000'],
            'allow_guests' => ['boolean'],
            'theme' => ['required', Rule::enum(QuizTheme::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_guests' => $this->boolean('allow_guests'),
            'theme' => $this->input('theme', QuizTheme::Standard->value),
        ]);
    }
}
