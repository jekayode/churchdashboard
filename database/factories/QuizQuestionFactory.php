<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
final class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'position' => 1,
            'text' => $this->faker->sentence().'?',
            'time_limit_seconds' => null,
            'points' => null,
        ];
    }
}
