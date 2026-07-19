<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuizAnswer;
use App\Models\QuizParticipant;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAnswer>
 */
final class QuizAnswerFactory extends Factory
{
    protected $model = QuizAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_question_id' => QuizQuestion::factory(),
            'quiz_participant_id' => QuizParticipant::factory(),
            'quiz_option_id' => null,
            'response_ms' => 3000,
            'is_correct' => false,
            'points_awarded' => 0,
        ];
    }
}
