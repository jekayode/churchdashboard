<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EventReport;
use App\Models\Projection;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class ProjectionService
{
    public function __construct(private CacheRepository $cache) {}

    /**
     * Default quarter weights in percent [Q1,Q2,Q3,Q4].
     */
    public static function defaultQuarterWeights(): array
    {
        return [15, 20, 30, 35];
    }

    /**
     * Distribute a yearly total into quarters using weights.
     * Uses Largest Remainder Method to ensure sum of quarters == total.
     *
     * @param  int  $total  Year total (>=0)
     * @param  array  $weights  Percent integers summing to 100
     * @return array<int,int> [q1,q2,q3,q4]
     */
    public function distributeToQuarters(int $total, ?array $weights = null): array
    {
        $weights = $weights ?: self::defaultQuarterWeights();
        $weights = array_values($weights);
        if (count($weights) !== 4 || array_sum($weights) !== 100) {
            $weights = self::defaultQuarterWeights();
        }

        $raw = [];
        $floors = [];
        $remainders = [];
        foreach ($weights as $i => $w) {
            $value = ($total * $w) / 100;
            $raw[$i] = $value;
            $floors[$i] = (int) floor($value);
            $remainders[$i] = $value - $floors[$i];
        }

        $allocated = array_sum($floors);
        $left = $total - $allocated;

        // Assign remaining units to largest remainders
        arsort($remainders, SORT_NUMERIC);
        foreach (array_keys($remainders) as $idx) {
            if ($left <= 0) {
                break;
            }
            $floors[$idx]++;
            $left--;
        }

        return array_values($floors);
    }

    /**
     * Compute branch actuals for a given year (or custom range).
     * Returns totals for attendance, guests, converts.
     */
    public function computeBranchActuals(int $branchId, int $year, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = sprintf('proj:actuals:branch:%d:%s:%s', $branchId, $startDate ?: (string) $year, $endDate ?: '');

        return $this->cache->remember($key, 900, function () use ($branchId, $year, $startDate, $endDate) {
            $query = EventReport::query()
                ->whereIn('event_reports.event_type', ['service', 'Sunday Service', 'Mid-Week Service'])
                ->join('events', 'events.id', '=', 'event_reports.event_id')
                ->where('events.branch_id', $branchId);

            if ($startDate && $endDate) {
                $query->whereBetween('report_date', [$startDate, $endDate]);
            } else {
                $query->whereYear('report_date', $year);
            }

            $rows = $query->get([
                'event_reports.attendance_male', 'event_reports.attendance_female', 'event_reports.attendance_children', 'event_reports.attendance_online',
                'event_reports.first_time_guests', 'event_reports.second_service_first_time_guests',
                'event_reports.converts', 'event_reports.second_service_converts',
                'event_reports.second_service_attendance_male', 'event_reports.second_service_attendance_female', 'event_reports.second_service_attendance_children',
            ]);

            $attendance = 0;
            $guests = 0;
            $converts = 0;
            $reportCount = 0;

            foreach ($rows as $r) {
                $attendance += (int) $r->attendance_male + (int) $r->attendance_female + (int) $r->attendance_children + (int) $r->attendance_online;
                $attendance += (int) ($r->second_service_attendance_male ?? 0) + (int) ($r->second_service_attendance_female ?? 0) + (int) ($r->second_service_attendance_children ?? 0);
                $guests += (int) ($r->first_time_guests ?? 0) + (int) ($r->second_service_first_time_guests ?? 0);
                $converts += (int) ($r->converts ?? 0) + (int) ($r->second_service_converts ?? 0);
                $reportCount++;
            }

            // Calculate weekly average attendance
            $weeklyAvgAttendance = $reportCount > 0 ? (int) round($attendance / $reportCount) : 0;

            return [
                'attendance' => $attendance,
                'guests' => $guests,
                'converts' => $converts,
                'weekly_avg_attendance' => $weeklyAvgAttendance,
            ];
        });
    }

    /**
     * Aggregate network actuals across branches.
     */
    public function computeNetworkActuals(int $year, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = sprintf('proj:actuals:network:%s:%s', $startDate ?: (string) $year, $endDate ?: '');

        return $this->cache->remember($key, 900, function () use ($year, $startDate, $endDate) {
            $query = EventReport::query()->whereIn('event_type', ['service', 'Sunday Service', 'Mid-Week Service']);

            if ($startDate && $endDate) {
                $query->whereBetween('report_date', [$startDate, $endDate]);
            } else {
                $query->whereYear('report_date', $year);
            }

            $rows = $query->get([
                'attendance_male', 'attendance_female', 'attendance_children', 'attendance_online',
                'first_time_guests', 'second_service_first_time_guests',
                'converts', 'second_service_converts',
                'second_service_attendance_male', 'second_service_attendance_female', 'second_service_attendance_children',
            ]);

            $attendance = 0;
            $guests = 0;
            $converts = 0;
            $reportCount = 0;

            foreach ($rows as $r) {
                $attendance += (int) $r->attendance_male + (int) $r->attendance_female + (int) $r->attendance_children + (int) $r->attendance_online;
                $attendance += (int) ($r->second_service_attendance_male ?? 0) + (int) ($r->second_service_attendance_female ?? 0) + (int) ($r->second_service_attendance_children ?? 0);
                $guests += (int) ($r->first_time_guests ?? 0) + (int) ($r->second_service_first_time_guests ?? 0);
                $converts += (int) ($r->converts ?? 0) + (int) ($r->second_service_converts ?? 0);
                $reportCount++;
            }

            // Calculate weekly average attendance
            $weeklyAvgAttendance = $reportCount > 0 ? (int) round($attendance / $reportCount) : 0;

            return [
                'attendance' => $attendance,
                'guests' => $guests,
                'converts' => $converts,
                'weekly_avg_attendance' => $weeklyAvgAttendance,
            ];
        });
    }

    /**
     * Calculate deltas percentage, safe for zero baseline.
     */
    public static function deltaPercent(int|float $current, int|float $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Build quarter table (current vs last year) for attendance/guests/converts.
     */
    public function quarterComparison(int $branchId, int $year): array
    {
        $result = [];
        for ($q = 1; $q <= 4; $q++) {
            $period = $this->quarterDates($year, $q);
            $curr = $this->computeBranchActuals($branchId, $year, $period['start'], $period['end']);
            $prev = $this->computeBranchActuals($branchId, $year - 1, $this->quarterDates($year - 1, $q)['start'], $this->quarterDates($year - 1, $q)['end']);
            $result[] = [
                'quarter' => 'Q'.$q,
                'current' => $curr,
                'previous' => $prev,
                'delta' => [
                    'attendance' => self::deltaPercent($curr['attendance'], $prev['attendance']),
                    'guests' => self::deltaPercent($curr['guests'], $prev['guests']),
                    'converts' => self::deltaPercent($curr['converts'], $prev['converts']),
                ],
            ];
        }

        return $result;
    }

    public function quarterDates(int $year, int $quarter): array
    {
        $startMonth = [1 => 1, 2 => 4, 3 => 7, 4 => 10][$quarter] ?? 1;
        $start = CarbonImmutable::create($year, $startMonth, 1)->startOfMonth();
        $end = $start->addMonths(2)->endOfMonth();

        return ['start' => $start->toDateString(), 'end' => $end->toDateString()];
    }

    /**
     * Fill projection quarter JSON arrays based on totals and weights.
     */
    public function fillProjectionQuarters(Projection $projection, ?array $weights = null): Projection
    {
        $weights = $weights ?: self::defaultQuarterWeights();
        $projection->quarterly_attendance = $this->distributeToQuarters((int) $projection->attendance_target, $weights);
        $projection->quarterly_converts = $this->distributeToQuarters((int) $projection->converts_target, $weights);
        $projection->quarterly_leaders = $this->distributeToQuarters((int) ($projection->leaders_target ?? 0), $weights);
        $projection->quarterly_volunteers = $this->distributeToQuarters((int) ($projection->volunteers_target ?? 0), $weights);

        return $projection;
    }
}
