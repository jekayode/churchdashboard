<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+3 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +4 hours');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'location' => $this->faker->address,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'frequency' => $this->faker->randomElement(['once', 'weekly', 'monthly']),
            'registration_type' => $this->faker->randomElement(['link', 'custom_form']),
            'status' => 'published',
            'branch_id' => 1, // Will be overridden in tests
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
