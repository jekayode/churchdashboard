<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Branch',
            'venue' => $this->faker->address,
            'service_time' => $this->faker->randomElement(['9:00 AM', '10:30 AM', '6:00 PM']),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
