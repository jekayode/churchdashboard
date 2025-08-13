<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
final class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departmentNames = [
            'Worship Team',
            'Sound Engineering',
            'Video Production',
            'Lighting',
            'Stage Management',
            'Sunday School Teachers',
            'Nursery Care',
            'Children Activities',
            'Teen Programs',
            'Young Adults',
            'Prayer Warriors',
            'Intercessors',
            'Street Evangelism',
            'Community Outreach',
            'Discipleship Training',
            'Bible Study Leaders',
            'Women\'s Fellowship',
            'Ladies Ministry',
            'Men\'s Brotherhood',
            'Men\'s Fellowship',
            'Senior Care',
            'Golden Age Ministry',
            'Social Media',
            'Photography',
            'Welcome Team',
            'Ushering',
            'Pastoral Care',
            'Counseling Support'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($departmentNames),
            'description' => $this->faker->paragraph(2),
            'ministry_id' => null, // Will be set explicitly in tests
            'leader_id' => null, // Will be set explicitly in tests if needed
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the department with a leader.
     */
    public function withLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'leader_id' => User::factory(),
        ]);
    }

    /**
     * Configure the department for a specific ministry.
     */
    public function forMinistry(Ministry $ministry): static
    {
        return $this->state(fn (array $attributes) => [
            'ministry_id' => $ministry->id,
        ]);
    }
}
