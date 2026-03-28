<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s').' +4 hours');
        $name = $this->faker->sentence(3);

        return [
            'branch_id' => Branch::factory(),
            'name' => $name,
            'public_slug' => Str::slug($name).'-'.$this->faker->unique()->numerify('####'),
            'description' => $this->faker->paragraph,
            'type' => 'other',
            'location' => $this->faker->address,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'frequency' => 'once',
            'registration_type' => 'simple',
            'status' => 'active',
            'is_public' => false,
            'is_recurring' => false,
            'is_recurring_instance' => false,
        ];
    }
}
