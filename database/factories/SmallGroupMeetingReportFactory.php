<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SmallGroup;
use App\Models\SmallGroupMeetingReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmallGroupMeetingReport>
 */
final class SmallGroupMeetingReportFactory extends Factory
{
    protected $model = SmallGroupMeetingReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maleAttendance = fake()->numberBetween(0, 15);
        $femaleAttendance = fake()->numberBetween(0, 20);
        $childrenAttendance = fake()->numberBetween(0, 8);
        $totalAttendance = $maleAttendance + $femaleAttendance + $childrenAttendance;
        
        $firstTimeGuests = fake()->numberBetween(0, min(5, $totalAttendance));
        $converts = fake()->numberBetween(0, min(3, $firstTimeGuests));

        $attendeeNames = [];
        for ($i = 0; $i < $totalAttendance; $i++) {
            $attendeeNames[] = fake()->name();
        }

        return [
            'small_group_id' => SmallGroup::factory(),
            'reported_by' => User::factory(),
            'meeting_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'meeting_time' => fake()->time('H:i'),
            'meeting_location' => fake()->randomElement([
                'Church Hall',
                'Community Center',
                'Member\'s Home',
                'Park Pavilion',
                'School Classroom',
                'Online Meeting',
            ]),
            'male_attendance' => $maleAttendance,
            'female_attendance' => $femaleAttendance,
            'children_attendance' => $childrenAttendance,
            'first_time_guests' => $firstTimeGuests,
            'converts' => $converts,
            'total_attendance' => $totalAttendance,
            'meeting_notes' => fake()->optional(0.8)->paragraphs(2, true),
            'prayer_requests' => fake()->optional(0.6)->paragraphs(1, true),
            'testimonies' => fake()->optional(0.4)->paragraphs(1, true),
            'attendee_names' => $attendeeNames,
            'status' => fake()->randomElement(['submitted', 'approved', 'rejected']),
            'rejection_reason' => null,
            'submitted_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'approved_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'approved_by' => fake()->optional(0.7)->randomElement(User::pluck('id')->toArray() ?: [1]),
        ];
    }

    /**
     * Create a report with approved status.
     */
    public function approved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_at' => fake()->dateTimeBetween('-1 month', 'now'),
                'approved_by' => User::factory(),
                'rejection_reason' => null,
            ];
        });
    }

    /**
     * Create a report with rejected status.
     */
    public function rejected(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejection_reason' => fake()->sentence(),
                'approved_at' => null,
                'approved_by' => null,
            ];
        });
    }

    /**
     * Create a report with submitted status (pending approval).
     */
    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'submitted',
                'approved_at' => null,
                'approved_by' => null,
                'rejection_reason' => null,
            ];
        });
    }

    /**
     * Create a report with high attendance.
     */
    public function highAttendance(): Factory
    {
        return $this->state(function (array $attributes) {
            $maleAttendance = fake()->numberBetween(15, 25);
            $femaleAttendance = fake()->numberBetween(20, 30);
            $childrenAttendance = fake()->numberBetween(8, 15);
            $totalAttendance = $maleAttendance + $femaleAttendance + $childrenAttendance;
            
            $firstTimeGuests = fake()->numberBetween(3, 8);
            $converts = fake()->numberBetween(1, 5);

            $attendeeNames = [];
            for ($i = 0; $i < $totalAttendance; $i++) {
                $attendeeNames[] = fake()->name();
            }

            return [
                'male_attendance' => $maleAttendance,
                'female_attendance' => $femaleAttendance,
                'children_attendance' => $childrenAttendance,
                'total_attendance' => $totalAttendance,
                'first_time_guests' => $firstTimeGuests,
                'converts' => $converts,
                'attendee_names' => $attendeeNames,
            ];
        });
    }

    /**
     * Create a report with recent date.
     */
    public function recent(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'meeting_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
                'submitted_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    /**
     * Create a report for a specific small group.
     */
    public function forSmallGroup(SmallGroup $smallGroup): Factory
    {
        return $this->state(function (array $attributes) use ($smallGroup) {
            return [
                'small_group_id' => $smallGroup->id,
            ];
        });
    }

    /**
     * Create a report by a specific user.
     */
    public function reportedBy(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'reported_by' => $user->id,
            ];
        });
    }
}
