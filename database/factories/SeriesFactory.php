<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Series>
 */
final class SeriesFactory extends Factory
{
    protected $model = Series::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'branch_id' => null,
            'name' => ucwords($name),
            'description' => $this->faker->sentence(),
            'tone' => $this->faker->randomElement(['orange', 'purple', 'amber', 'lemon']),
            'starts_on' => now()->subMonths(2),
            'ends_on' => null,
            'is_published' => true,
        ];
    }

    public function unpublished(): self
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
