<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QuizParticipant>
 */
final class QuizParticipantFactory extends Factory
{
    protected $model = QuizParticipant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'member_id' => null,
            'guest_token' => Str::random(40),
            'display_name' => $this->faker->firstName(),
            'joined_at' => now(),
        ];
    }
}
