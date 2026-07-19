<?php

declare(strict_types=1);

namespace App\Http\Requests\Pastor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class QuizQuestionsRequest extends FormRequest
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
            'questions' => ['present', 'array', 'max:50'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.text' => ['required', 'string', 'max:500'],
            'questions.*.time_limit_seconds' => ['nullable', 'integer', 'min:5', 'max:120'],
            'questions.*.points' => ['nullable', 'integer', 'min:100', 'max:5000'],
            // Two options is the minimum that is still a question; four is all
            // that fits legibly on a projector and on a phone.
            'questions.*.options' => ['required', 'array', 'min:2', 'max:4'],
            'questions.*.options.*.text' => ['required', 'string', 'max:120'],
            'questions.*.correct' => ['required', 'integer', 'min:0', 'max:3'],
        ];
    }

    /**
     * A question whose marked answer is a blank option would be unanswerable on
     * the night, and the per-field rules cannot see across to the index.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('questions', []) as $i => $question) {
                $correct = $question['correct'] ?? null;
                $options = array_values($question['options'] ?? []);

                if ($correct === null || ! isset($options[$correct])) {
                    $validator->errors()->add(
                        "questions.{$i}.correct",
                        'Choose which answer is correct.',
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'questions.*.text.required' => 'Every question needs its wording.',
            'questions.*.options.min' => 'A question needs at least two answers.',
        ];
    }
}
