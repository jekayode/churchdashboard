<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProjectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PerformanceController extends Controller
{
    public function __construct(private ProjectionService $service) {}

    public function branch(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = (int) ($request->get('branch_id') ?: optional($user->pastoredBranches()->first())->id);
        abort_if($branchId === 0, 403, 'Branch not specified');

        $year = (int) ($request->get('year') ?: now()->year);
        $range = $request->get('range', 'YTD');
        $compare = $request->get('compare');
        $start = $request->get('start_date');
        $end = $request->get('end_date');

        // Determine date range based on range parameter
        $dateRange = $this->getDateRange($year, $range, $start, $end);

        $actuals = $this->service->computeBranchActuals($branchId, $year, $dateRange['start'], $dateRange['end']);

        $data = [
            'branch_id' => $branchId,
            'year' => $year,
            'range' => $range,
            'actuals' => $actuals,
            'quarters' => $this->service->quarterComparison($branchId, $year),
            'monthly' => $this->getMonthlyData($branchId, $year),
            'yearly' => $this->getYearlyData($branchId, $year),
            'projections' => $this->getProjectionComparison($branchId, $year),
            'quarterly_progress' => $this->getQuarterlyProgress($branchId, $year),
            'achievement_summary' => $this->getAchievementSummary($branchId, $year),
            'insights' => $this->getInsights($branchId, $year),
        ];

        // Add comparison data if requested
        if ($compare) {
            $comparisonData = $this->getComparisonData($branchId, $year, $range, $compare, $actuals);
            $data['comparison'] = $comparisonData;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function network(Request $request): JsonResponse
    {
        $year = (int) ($request->get('year') ?: now()->year);
        $range = $request->get('range', 'YTD');
        $compare = $request->get('compare');
        $start = $request->get('start_date');
        $end = $request->get('end_date');

        // Determine date range based on range parameter
        $dateRange = $this->getDateRange($year, $range, $start, $end);

        $actuals = $this->service->computeNetworkActuals($year, $dateRange['start'], $dateRange['end']);

        // Get branch-specific performance data
        $branches = \App\Models\Branch::all();
        $branchPerformance = [];

        // Eager load projections for all branches
        $projections = \App\Models\Projection::whereIn('branch_id', $branches->pluck('id'))
            ->where('year', $year)
            ->where('is_current_year', true)
            ->get()
            ->keyBy('branch_id');

        foreach ($branches as $branch) {
            $branchActuals = $this->service->computeBranchActuals($branch->id, $year, $dateRange['start'], $dateRange['end']);
            $projection = $projections->get($branch->id);

            $branchPerformance[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'actuals' => $branchActuals,
                'projection' => $projection ? [
                    'weekly_avg_attendance_target' => $projection->weekly_avg_attendance_target,
                ] : null,
            ];
        }

        $data = [
            'year' => $year,
            'range' => $range,
            'actuals' => $actuals,
            'branches' => $branchPerformance,
        ];

        // Add comparison data if requested
        if ($compare) {
            $comparisonData = $this->getNetworkComparisonData($year, $range, $compare, $actuals);
            $data['comparison'] = $comparisonData;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get date range based on range parameter.
     */
    private function getDateRange(int $year, string $range, ?string $start, ?string $end): array
    {
        // If custom dates provided, use them
        if ($start && $end) {
            return ['start' => $start, 'end' => $end];
        }

        $now = now();

        return match ($range) {
            'YTD' => [
                'start' => "{$year}-01-01",
                'end' => $now->year === $year ? $now->toDateString() : "{$year}-12-31",
            ],
            'QTD' => [
                'start' => $this->getQuarterStart($year, $now->year === $year ? $now->quarter : 4),
                'end' => $now->year === $year ? $now->toDateString() : $this->getQuarterEnd($year, $now->year === $year ? $now->quarter : 4),
            ],
            'MTD' => [
                'start' => "{$year}-".($now->year === $year ? $now->format('m') : '12').'-01',
                'end' => $now->year === $year ? $now->toDateString() : "{$year}-12-31",
            ],
            'custom' => [
                'start' => $start ?? "{$year}-01-01",
                'end' => $end ?? "{$year}-12-31",
            ],
            default => [
                'start' => "{$year}-01-01",
                'end' => "{$year}-12-31",
            ]
        };
    }

    /**
     * Get comparison data for branch performance.
     */
    private function getComparisonData(int $branchId, int $year, string $range, string $compare, array $currentActuals): array
    {
        $currentRange = $this->getDateRange($year, $range, null, null);
        $previousRange = $this->getPreviousPeriodRange($year, $range, $compare, $currentRange);
        $previousActuals = $this->service->computeBranchActuals($branchId, $year, $previousRange['start'], $previousRange['end']);

        return [
            'type' => $compare,
            'previous_period' => $previousRange,
            'previous_actuals' => $previousActuals,
            'deltas' => [
                'attendance' => $this->service::deltaPercent($currentActuals['attendance'], $previousActuals['attendance']),
                'guests' => $this->service::deltaPercent($currentActuals['guests'], $previousActuals['guests']),
                'converts' => $this->service::deltaPercent($currentActuals['converts'], $previousActuals['converts']),
                'weekly_avg_attendance' => $this->service::deltaPercent($currentActuals['weekly_avg_attendance'] ?? 0, $previousActuals['weekly_avg_attendance'] ?? 0),
            ],
        ];
    }

    /**
     * Get comparison data for network performance.
     */
    private function getNetworkComparisonData(int $year, string $range, string $compare, array $currentActuals): array
    {
        $currentRange = $this->getDateRange($year, $range, null, null);
        $previousRange = $this->getPreviousPeriodRange($year, $range, $compare, $currentRange);
        $previousActuals = $this->service->computeNetworkActuals($year, $previousRange['start'], $previousRange['end']);

        return [
            'type' => $compare,
            'previous_period' => $previousRange,
            'previous_actuals' => $previousActuals,
            'deltas' => [
                'attendance' => $this->service::deltaPercent($currentActuals['attendance'], $previousActuals['attendance']),
                'guests' => $this->service::deltaPercent($currentActuals['guests'], $previousActuals['guests']),
                'converts' => $this->service::deltaPercent($currentActuals['converts'], $previousActuals['converts']),
                'weekly_avg_attendance' => $this->service::deltaPercent($currentActuals['weekly_avg_attendance'] ?? 0, $previousActuals['weekly_avg_attendance'] ?? 0),
            ],
        ];
    }

    /**
     * Get previous period range for comparison.
     */
    private function getPreviousPeriodRange(int $year, string $range, string $compare, array $currentRange): array
    {
        $yearString = (string) $year;
        $prevYearString = (string) ($year - 1);

        return match ($compare) {
            'yoy' => [
                'start' => str_replace($yearString, $prevYearString, $currentRange['start']),
                'end' => str_replace($yearString, $prevYearString, $currentRange['end']),
            ],
            'qoq' => $this->getPreviousQuarterRange($year, $currentRange),
            'mom' => $this->getPreviousMonthRange($year, $currentRange),
            default => [
                'start' => str_replace($yearString, $prevYearString, $currentRange['start']),
                'end' => str_replace($yearString, $prevYearString, $currentRange['end']),
            ]
        };
    }

    /**
     * Get previous quarter range.
     */
    private function getPreviousQuarterRange(int $year, array $currentRange): array
    {
        $currentStart = \Carbon\Carbon::parse($currentRange['start']);
        $previousQuarter = $currentStart->subQuarter();

        return [
            'start' => $previousQuarter->startOfQuarter()->toDateString(),
            'end' => $previousQuarter->endOfQuarter()->toDateString(),
        ];
    }

    /**
     * Get previous month range.
     */
    private function getPreviousMonthRange(int $year, array $currentRange): array
    {
        $currentStart = \Carbon\Carbon::parse($currentRange['start']);
        $previousMonth = $currentStart->subMonth();

        return [
            'start' => $previousMonth->startOfMonth()->toDateString(),
            'end' => $previousMonth->endOfMonth()->toDateString(),
        ];
    }

    /**
     * Get quarter start date.
     */
    private function getQuarterStart(int $year, int $quarter): string
    {
        $startMonth = [1 => 1, 2 => 4, 3 => 7, 4 => 10][$quarter] ?? 1;

        return \Carbon\Carbon::create($year, $startMonth, 1)->startOfMonth()->toDateString();
    }

    /**
     * Get quarter end date.
     */
    private function getQuarterEnd(int $year, int $quarter): string
    {
        $startMonth = [1 => 1, 2 => 4, 3 => 7, 4 => 10][$quarter] ?? 1;

        return \Carbon\Carbon::create($year, $startMonth, 1)->addMonths(2)->endOfMonth()->toDateString();
    }

    /**
     * Get monthly data for the year.
     */
    private function getMonthlyData(int $branchId, int $year): array
    {
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

            $actuals = $this->service->computeBranchActuals($branchId, $year, $startDate, $endDate);

            // Get previous month for comparison
            $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
            $prevStartDate = $prevMonth->startOfMonth()->toDateString();
            $prevEndDate = $prevMonth->endOfMonth()->toDateString();
            $prevActuals = $this->service->computeBranchActuals($branchId, $prevMonth->year, $prevStartDate, $prevEndDate);

            $monthlyData[] = [
                'month' => \Carbon\Carbon::create($year, $month, 1)->format('M Y'),
                'attendance' => $actuals['attendance'],
                'guests' => $actuals['guests'],
                'converts' => $actuals['converts'],
                'weekly_avg' => $actuals['weekly_avg_attendance'] ?? 0,
                'delta' => $this->service::deltaPercent($actuals['attendance'], $prevActuals['attendance']),
            ];
        }

        return $monthlyData;
    }

    /**
     * Get yearly data for trend analysis.
     */
    private function getYearlyData(int $branchId, int $currentYear): array
    {
        $yearlyData = [];

        for ($year = $currentYear - 3; $year <= $currentYear; $year++) {
            $actuals = $this->service->computeBranchActuals($branchId, $year);

            $yearlyData[] = [
                'year' => $year,
                'attendance' => $actuals['attendance'],
                'guests' => $actuals['guests'],
                'converts' => $actuals['converts'],
                'growth' => $year === $currentYear - 3 ? 0 : $this->service::deltaPercent(
                    $actuals['attendance'],
                    $yearlyData[count($yearlyData) - 1]['attendance'] ?? 0
                ),
            ];
        }

        return $yearlyData;
    }

    /**
     * Get projection comparison data.
     */
    private function getProjectionComparison(int $branchId, int $year): array
    {
        $projection = \App\Models\Projection::where('branch_id', $branchId)
            ->where('year', $year)
            ->where('is_current_year', true)
            ->first();

        if (! $projection) {
            return [];
        }

        $actuals = $this->service->computeBranchActuals($branchId, $year);

        return [
            [
                'metric' => 'Cumulative Attendance',
                'target' => $projection->attendance_target,
                'actual' => $actuals['attendance'],
                'progress' => $projection->attendance_target > 0 ?
                    round(($actuals['attendance'] / $projection->attendance_target) * 100, 1) : 0,
            ],
            [
                'metric' => 'Guests',
                'target' => $projection->guests_target ?? 0,
                'actual' => $actuals['guests'],
                'progress' => ($projection->guests_target ?? 0) > 0 ?
                    round(($actuals['guests'] / $projection->guests_target) * 100, 1) : 0,
            ],
            [
                'metric' => 'Converts',
                'target' => $projection->converts_target,
                'actual' => $actuals['converts'],
                'progress' => $projection->converts_target > 0 ?
                    round(($actuals['converts'] / $projection->converts_target) * 100, 1) : 0,
            ],
            [
                'metric' => 'Weekly Avg Attendance',
                'target' => $projection->weekly_avg_attendance_target ?? 0,
                'actual' => $actuals['weekly_avg_attendance'] ?? 0,
                'progress' => ($projection->weekly_avg_attendance_target ?? 0) > 0 ?
                    round((($actuals['weekly_avg_attendance'] ?? 0) / $projection->weekly_avg_attendance_target) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get quarterly progress data.
     * Compares actual average weekly attendance for the quarter vs projected quarterly attendance target.
     * Q1: Jan-Mar actual weekly avg vs Q1 Attendance target
     * Q2: Apr-Jun actual weekly avg vs Q2 Attendance target
     * Q3: Jul-Sep actual weekly avg vs Q3 Attendance target
     * Q4: Oct-Dec actual weekly avg vs Q4 Attendance target
     */
    private function getQuarterlyProgress(int $branchId, int $year): array
    {
        $projection = \App\Models\Projection::where('branch_id', $branchId)
            ->where('year', $year)
            ->where('is_current_year', true)
            ->first();

        if (! $projection) {
            return [];
        }

        $quarterlyProgress = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startDate = $this->getQuarterStart($year, $quarter);
            $endDate = $this->getQuarterEnd($year, $quarter);

            // Get actual average weekly attendance for this quarter (Jan-Mar, Apr-Jun, etc.)
            $actuals = $this->service->computeBranchActuals($branchId, $year, $startDate, $endDate);
            $actualWeeklyAvg = $actuals['weekly_avg_attendance'] ?? 0;

            // Get projected quarterly attendance target directly from quarterly_attendance array
            // This is the value entered in the Quarterly Breakdown form (e.g., Q1: 200, Q2: 250)
            $projectedQuarterlyAttendance = $projection->quarterly_attendance[$quarter - 1] ?? 0;

            // Calculate progress: actual weekly average vs projected quarterly attendance target
            $progress = $projectedQuarterlyAttendance > 0 ? round(($actualWeeklyAvg / $projectedQuarterlyAttendance) * 100, 1) : 0;

            $quarterlyProgress[] = [
                'quarter' => "Q{$quarter}",
                'progress' => $progress,
            ];
        }

        return $quarterlyProgress;
    }

    /**
     * Get achievement summary.
     */
    private function getAchievementSummary(int $branchId, int $year): string
    {
        $projection = \App\Models\Projection::where('branch_id', $branchId)
            ->where('year', $year)
            ->where('is_current_year', true)
            ->first();

        if (! $projection) {
            return 'No projection data available for this year.';
        }

        $actuals = $this->service->computeBranchActuals($branchId, $year);

        $attendanceProgress = $projection->attendance_target > 0 ?
            ($actuals['attendance'] / $projection->attendance_target) * 100 : 0;
        $convertsProgress = $projection->converts_target > 0 ?
            ($actuals['converts'] / $projection->converts_target) * 100 : 0;

        $achieved = 0;
        if ($attendanceProgress >= 100) {
            $achieved++;
        }
        if ($convertsProgress >= 100) {
            $achieved++;
        }

        // Calculate overall progress as weighted average (attendance is more important)
        $overallProgress = ($attendanceProgress * 0.7) + ($convertsProgress * 0.3);

        return "Achieved {$achieved} out of 2 main targets. ".
               'Overall progress: '.round($overallProgress, 1).'%';
    }

    /**
     * Get insights for the year.
     */
    private function getInsights(int $branchId, int $year): array
    {
        $insights = [];

        // Get current year data
        $currentActuals = $this->service->computeBranchActuals($branchId, $year);

        // Get previous year data for comparison
        $previousActuals = $this->service->computeBranchActuals($branchId, $year - 1);

        // Calculate growth
        $attendanceGrowth = $this->service::deltaPercent($currentActuals['attendance'], $previousActuals['attendance']);
        $convertsGrowth = $this->service::deltaPercent($currentActuals['converts'], $previousActuals['converts']);

        if ($attendanceGrowth > 0) {
            $insights[] = "Attendance increased by {$attendanceGrowth}% compared to last year";
        } elseif ($attendanceGrowth < 0) {
            $insights[] = 'Attendance decreased by '.abs($attendanceGrowth).'% compared to last year';
        }

        if ($convertsGrowth > 0) {
            $insights[] = "Converts increased by {$convertsGrowth}% compared to last year";
        } elseif ($convertsGrowth < 0) {
            $insights[] = 'Converts decreased by '.abs($convertsGrowth).'% compared to last year';
        }

        // Add projection insights
        $projection = \App\Models\Projection::where('branch_id', $branchId)
            ->where('year', $year)
            ->where('is_current_year', true)
            ->first();

        if ($projection) {
            $attendanceProgress = $projection->attendance_target > 0 ?
                ($currentActuals['attendance'] / $projection->attendance_target) * 100 : 0;

            if ($attendanceProgress >= 100) {
                $insights[] = 'Attendance target has been achieved!';
            } elseif ($attendanceProgress >= 75) {
                $insights[] = 'Attendance target is on track for achievement';
            } elseif ($attendanceProgress >= 50) {
                $insights[] = 'Attendance target needs attention to meet goal';
            } else {
                $insights[] = 'Attendance target is at risk of not being met';
            }
        }

        return $insights;
    }
}
