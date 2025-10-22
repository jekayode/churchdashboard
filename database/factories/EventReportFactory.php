<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventReport>
 */
class EventReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'reported_by' => \App\Models\User::factory(),
            'attendance_male' => $this->faker->numberBetween(10, 100),
            'attendance_female' => $this->faker->numberBetween(10, 100),
            'attendance_children' => $this->faker->numberBetween(5, 50),
            'attendance_online' => $this->faker->numberBetween(0, 30),
            'first_time_guests' => $this->faker->numberBetween(0, 20),
            'converts' => $this->faker->numberBetween(0, 10),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'number_of_cars' => $this->faker->numberBetween(10, 100),
            'notes' => $this->faker->optional()->sentence(),
            'report_date' => $this->faker->date(),
            'is_multi_service' => false,
            'second_service_attendance_male' => 0,
            'second_service_attendance_female' => 0,
            'second_service_attendance_children' => 0,
            'second_service_attendance_online' => 0,
            'second_service_first_time_guests' => 0,
            'second_service_converts' => 0,
            'second_service_number_of_cars' => 0,
            'second_service_start_time' => null,
            'second_service_end_time' => null,
            'event_type' => $this->faker->randomElement(\App\Models\EventReport::EVENT_TYPES),
            'service_type' => $this->faker->randomElement(['Sunday Service', 'MidWeek', 'Conferences', 'Outreach', 'Evangelism (Beautiful Feet)', 'Water Baptism', 'TECi', 'Membership Class', 'LifeGroup Meeting', 'other']),
            'second_service_notes' => null,
        ];
    }
}
