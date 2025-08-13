<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
final class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'branch_id' => Branch::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date(),
            'anniversary' => $this->faker->optional()->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'separated', 'widowed', 'in_a_relationship', 'engaged']),
            'occupation' => $this->faker->optional()->jobTitle(),
            'nearest_bus_stop' => $this->faker->optional()->streetName(),
            'date_joined' => $this->faker->date(),
            'date_attended_membership_class' => $this->faker->optional()->date(),
            'teci_status' => $this->faker->randomElement([
                'not_started', '100_level', '200_level', '300_level', 
                '400_level', '500_level', 'graduated', 'paused'
            ]),
            'growth_level' => $this->faker->randomElement(['core', 'pastor', 'growing', 'new_believer']),
            'leadership_trainings' => null,
            'member_status' => $this->faker->randomElement(['visitor', 'member', 'volunteer', 'leader', 'minister']),
        ];
    }

    /**
     * Indicate that the member is a leader.
     */
    public function leader(): static
    {
        return $this->state(fn (array $attributes) => [
            'member_status' => 'leader',
            'growth_level' => 'core',
        ]);
    }

    /**
     * Indicate that the member is a minister.
     */
    public function minister(): static
    {
        return $this->state(fn (array $attributes) => [
            'member_status' => 'minister',
            'growth_level' => 'pastor',
        ]);
    }

    /**
     * Indicate that the member has a linked user account.
     */
    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }
}
