<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Member;
use App\Models\SmallGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmallGroup>
 */
final class SmallGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmallGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meetingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return [
            'branch_id' => Branch::factory(),
            'name' => $this->faker->words(3, true) . ' Small Group',
            'description' => $this->faker->paragraph(),
            'leader_id' => null, // Will be set manually in tests if needed
            'meeting_day' => $this->faker->randomElement($meetingDays),
            'meeting_time' => $this->faker->time('H:i'),
            'location' => $this->faker->address(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the small group is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the small group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }



    /**
     * Indicate that the small group has a specific leader.
     */
    public function withLeader(Member $leader): static
    {
        return $this->state(fn (array $attributes) => [
            'leader_id' => $leader->id,
            'branch_id' => $leader->branch_id,
        ]);
    }

    /**
     * Indicate that the small group belongs to a specific branch.
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branch->id,
        ]);
    }
} 