<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
final class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'title' => $this->faker->sentence(3),
            'status' => 'draft',
            'seconds_per_question' => 20,
            'base_points' => 1000,
            'reveal_seconds' => 6,
            'allow_guests' => true,
        ];
    }

    public function lobby(): static
    {
        return $this->state(fn (): array => [
            'status' => 'lobby',
            'code' => Quiz::generateCode(),
        ]);
    }

    public function running(?\DateTimeInterface $startedAt = null): static
    {
        return $this->state(fn (): array => [
            'status' => 'running',
            'code' => Quiz::generateCode(),
            'started_at' => $startedAt ?? now(),
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (): array => [
            'status' => 'finished',
            'finished_at' => now(),
        ]);
    }
}
