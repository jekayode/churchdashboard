<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sermon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sermon>
 */
final class SermonFactory extends Factory
{
    protected $model = Sermon::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'branch_id' => null,
            'series_id' => null,
            'title' => rtrim($title, '.'),
            'description' => $this->faker->paragraph(),
            'speaker' => $this->faker->name(),
            'speaker_member_id' => null,
            'preached_on' => now()->subWeek(),
            'duration_seconds' => $this->faker->numberBetween(1200, 3600),
            'tone' => $this->faker->randomElement(['orange', 'purple', 'amber', 'lemon']),
            'is_live' => false,
            'live_url' => null,
            'is_published' => true,
        ];
    }

    public function unpublished(): self
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function live(): self
    {
        return $this->state(fn (): array => [
            'is_live' => true,
            'live_url' => 'https://example.test/live',
        ]);
    }
}
