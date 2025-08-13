<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Projection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projection>
 */
final class ProjectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Projection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = now()->year;
        $year = $this->faker->numberBetween($currentYear - 2, $currentYear + 2);
        
        // Generate realistic targets
        $attendanceTarget = $this->faker->numberBetween(50, 500);
        $convertsTarget = $this->faker->numberBetween(10, 50);
        $leadersTarget = $this->faker->numberBetween(5, 30);
        $volunteersTarget = $this->faker->numberBetween(15, 60);
        
        // Generate quarterly breakdown (should sum to annual targets)
        $quarterlyAttendance = $this->distributeQuarterly($attendanceTarget);
        $quarterlyConverts = $this->distributeQuarterly($convertsTarget);
        $quarterlyLeaders = $this->distributeQuarterly($leadersTarget);
        $quarterlyVolunteers = $this->distributeQuarterly($volunteersTarget);

        return [
            'branch_id' => Branch::factory(),
            'year' => $year,
            'attendance_target' => $attendanceTarget,
            'converts_target' => $convertsTarget,
            'leaders_target' => $leadersTarget,
            'volunteers_target' => $volunteersTarget,
            'quarterly_breakdown' => [
                'Q1' => ['attendance' => $quarterlyAttendance[0], 'converts' => $quarterlyConverts[0], 'leaders' => $quarterlyLeaders[0], 'volunteers' => $quarterlyVolunteers[0]],
                'Q2' => ['attendance' => $quarterlyAttendance[1], 'converts' => $quarterlyConverts[1], 'leaders' => $quarterlyLeaders[1], 'volunteers' => $quarterlyVolunteers[1]],
                'Q3' => ['attendance' => $quarterlyAttendance[2], 'converts' => $quarterlyConverts[2], 'leaders' => $quarterlyLeaders[2], 'volunteers' => $quarterlyVolunteers[2]],
                'Q4' => ['attendance' => $quarterlyAttendance[3], 'converts' => $quarterlyConverts[3], 'leaders' => $quarterlyLeaders[3], 'volunteers' => $quarterlyVolunteers[3]],
            ],
            'monthly_breakdown' => $this->generateMonthlyBreakdown($quarterlyAttendance, $quarterlyConverts, $quarterlyLeaders, $quarterlyVolunteers),

            'status' => $this->faker->randomElement(['draft', 'in_review', 'approved', 'rejected']),
            'is_current_year' => $year === $currentYear ? $this->faker->boolean(30) : false,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the projection is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the projection is in review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the projection is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'approval_notes' => $this->faker->optional()->sentence(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the projection is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => null,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that this is the current year projection.
     */
    public function currentYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => now()->year,
            'is_current_year' => true,
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
        ]);
    }

    /**
     * Distribute a total target across 4 quarters with some realistic variation.
     */
    private function distributeQuarterly(int $total): array
    {
        // Base distribution: 20%, 25%, 30%, 25% with some randomness
        $baseDistribution = [0.20, 0.25, 0.30, 0.25];
        $quarters = [];
        $remaining = $total;
        
        for ($i = 0; $i < 3; $i++) {
            $variance = $this->faker->numberBetween(-5, 5) / 100; // ±5% variance
            $percentage = $baseDistribution[$i] + $variance;
            $percentage = max(0.1, min(0.5, $percentage)); // Keep within reasonable bounds
            
            $quarterValue = (int) round($total * $percentage);
            $quarters[] = $quarterValue;
            $remaining -= $quarterValue;
        }
        
        // Last quarter gets the remainder
        $quarters[] = max(0, $remaining);
        
        return $quarters;
    }

    /**
     * Generate monthly breakdown from quarterly data.
     */
    private function generateMonthlyBreakdown(array $quarterlyAttendance, array $quarterlyConverts, array $quarterlyLeaders, array $quarterlyVolunteers): array
    {
        $months = [];
        $monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        for ($quarter = 0; $quarter < 4; $quarter++) {
            $quarterMonths = array_slice($monthNames, $quarter * 3, 3);
            
            // Distribute quarterly targets across 3 months
            $attendanceMonthly = $this->distributeMonthly($quarterlyAttendance[$quarter]);
            $convertsMonthly = $this->distributeMonthly($quarterlyConverts[$quarter]);
            $leadersMonthly = $this->distributeMonthly($quarterlyLeaders[$quarter]);
            $volunteersMonthly = $this->distributeMonthly($quarterlyVolunteers[$quarter]);
            
            for ($month = 0; $month < 3; $month++) {
                $months[$quarterMonths[$month]] = [
                    'attendance' => $attendanceMonthly[$month],
                    'converts' => $convertsMonthly[$month],
                    'leaders' => $leadersMonthly[$month],
                    'volunteers' => $volunteersMonthly[$month],
                ];
            }
        }
        
        return $months;
    }

    /**
     * Distribute a quarterly total across 3 months.
     */
    private function distributeMonthly(int $quarterlyTotal): array
    {
        if ($quarterlyTotal === 0) {
            return [0, 0, 0];
        }
        
        // Roughly equal distribution with some variation
        $basePerMonth = (int) round($quarterlyTotal / 3);
        $months = [$basePerMonth, $basePerMonth, $basePerMonth];
        
        // Adjust for remainder
        $remainder = $quarterlyTotal - array_sum($months);
        if ($remainder > 0) {
            $months[1] += $remainder;
        } elseif ($remainder < 0) {
            $months[2] += $remainder;
            if ($months[2] < 0) {
                $months[1] += $months[2];
                $months[2] = 0;
            }
        }
        
        return array_map('max', $months, [0, 0, 0]); // Ensure no negative values
    }
} 