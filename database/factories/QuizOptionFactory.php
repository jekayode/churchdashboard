<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizOption>
 */
final class QuizOptionFactory extends Factory
{
    protected $model = QuizOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_question_id' => QuizQuestion::factory(),
            'position' => 1,
            'text' => $this->faker->word(),
            'is_correct' => false,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (): array => ['is_correct' => true]);
    }
}
