<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberStatusHistory>
 */
final class MemberStatusHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MemberStatusHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['visitor', 'member', 'volunteer', 'leader', 'minister'];
        $previousStatus = $this->faker->randomElement($statuses);
        
        // Ensure new status is different from previous
        $availableNewStatuses = array_filter($statuses, fn($status) => $status !== $previousStatus);
        $newStatus = $this->faker->randomElement($availableNewStatuses);

        $reasons = [
            'Promoted due to leadership role',
            'Completed membership requirements',
            'Started volunteering in ministry',
            'Assigned as department leader',
            'Regular attendance and commitment',
            'Completed leadership training',
            'Ministry assignment',
            'Status review and update',
            'Role change request',
            'Administrative update',
        ];

        return [
            'member_id' => Member::factory(),
            'changed_by' => User::factory(),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reason' => $this->faker->randomElement($reasons),
            'notes' => $this->faker->optional(0.6)->sentence(),
            'changed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create a status change for promotion.
     */
    public function promotion(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'previous_status' => 'member',
                'new_status' => 'volunteer',
                'reason' => 'Promoted due to active participation and commitment',
                'notes' => 'Member has shown consistent attendance and willingness to serve.',
            ];
        });
    }

    /**
     * Create a status change for leadership assignment.
     */
    public function leadership(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'previous_status' => 'volunteer',
                'new_status' => 'leader',
                'reason' => 'Assigned as department leader',
                'notes' => 'Member has demonstrated leadership qualities and completed training.',
            ];
        });
    }

    /**
     * Create a status change for ministry assignment.
     */
    public function ministry(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'previous_status' => 'leader',
                'new_status' => 'minister',
                'reason' => 'Assigned as ministry leader',
                'notes' => 'Member has been assigned to lead a ministry department.',
            ];
        });
    }

    /**
     * Create a recent status change.
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'changed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }
}
