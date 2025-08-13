<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ministry>
 */
final class MinistryFactory extends Factory
{
    protected $model = Ministry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ministryNames = [
            'Worship Ministry',
            'Youth Ministry',
            'Children Ministry',
            'Outreach Ministry',
            'Prayer Ministry',
            'Music Ministry',
            'Evangelism Ministry',
            'Discipleship Ministry',
            'Women Ministry',
            'Men Ministry',
            'Senior Ministry',
            'Media Ministry',
            'Hospitality Ministry',
            'Counseling Ministry'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($ministryNames),
            'description' => $this->faker->paragraph(2),
            'branch_id' => null, // Will be set explicitly in tests
            'leader_id' => null, // Will be set explicitly in tests if needed
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the ministry with a leader.
     */
    public function withLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'leader_id' => User::factory(),
        ]);
    }

    /**
     * Configure the ministry for a specific branch.
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branch->id,
        ]);
    }
}
