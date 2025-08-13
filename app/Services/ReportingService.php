<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\EventReport;
use App\Models\Event;
use App\Models\Member;
use App\Models\SmallGroup;
use App\Models\SmallGroupMeetingReport;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

final class ReportingService
{
    /**
     * Get dashboard statistics for a specific branch or all branches.
     */
    public function getDashboardStatistics(?int $branchId = null, ?string $period = 'month'): array
    {
        $cacheKey = "dashboard_stats_{$branchId}_{$period}";
        $cacheTags = ['dashboard_stats', "branch_{$branchId}", 'event_reports', 'members'];
        
        return Cache::remember($cacheKey, 3600, function () use ($branchId, $period) {
            $dateRange = $this->getDateRange($period);
            
            return [
                'members' => $this->getMemberStatistics($branchId, $dateRange),
                'events' => $this->getEventStatistics($branchId, $dateRange),
                'attendance' => $this->getAttendanceStatistics($branchId, $dateRange),
                'small_groups' => $this->getSmallGroupStatistics($branchId, $dateRange),
                'leadership' => $this->getLeadershipStatistics($branchId, $dateRange),
                'teci' => $this->getTeciStatistics($branchId, $dateRange),
                'trends' => $this->getTrendData($branchId, $dateRange),
            ];
        });
    }

    /**
     * Get dashboard statistics for a custom date range.
     */
    public function getDashboardStatisticsForDateRange(array $dateRange, ?int $branchId = null): array
    {
        $cacheKey = "dashboard_stats_custom_{$branchId}_{$dateRange['start']}_{$dateRange['end']}";
        $cacheTags = ['dashboard_stats', "branch_{$branchId}", 'event_reports', 'members'];
        
        return Cache::remember($cacheKey, 1800, function () use ($branchId, $dateRange) {
            // Convert string dates to Carbon instances
            $start = Carbon::parse($dateRange['start'])->startOfDay();
            $end = Carbon::parse($dateRange['end'])->endOfDay();
            
            $dateRangeCarbon = [
                'start' => $start,
                'end' => $end,
            ];
            
            return [
                'members' => $this->getMemberStatistics($branchId, $dateRangeCarbon),
                'events' => $this->getEventStatistics($branchId, $dateRangeCarbon),
                'attendance' => $this->getAttendanceStatistics($branchId, $dateRangeCarbon),
                'small_groups' => $this->getSmallGroupStatistics($branchId, $dateRangeCarbon),
                'leadership' => $this->getLeadershipStatistics($branchId, $dateRangeCarbon),
                'teci' => $this->getTeciStatistics($branchId, $dateRangeCarbon),
                'trends' => $this->getTrendData($branchId, $dateRangeCarbon),
            ];
        });
    }

    /**
     * Get comparative statistics between two periods.
     */
    public function getComparativeStatistics(?int $branchId, array $period1, array $period2): array
    {
        $stats1 = $this->getPeriodStatistics($branchId, $period1);
        $stats2 = $this->getPeriodStatistics($branchId, $period2);

        $percentages = $this->calculatePercentageChanges($stats1, $stats2);
        
        $result = [
            'period1' => $stats1,
            'period2' => $stats2,
            'changes' => $this->calculateChanges($stats1, $stats2),
            'percentages' => $percentages,
            'highest_attendance_period1' => $this->getHighestAttendanceEventForPeriod($branchId, $period1),
            'highest_attendance_period2' => $this->getHighestAttendanceEventForPeriod($branchId, $period2),
            // Frontend-compatible structure
            'attendance' => [
                'period1' => $stats1['attendance'],
                'period2' => $stats2['attendance'],
                'change' => $percentages['attendance'],
            ],
            'converts' => [
                'period1' => $stats1['converts'],
                'period2' => $stats2['converts'],
                'change' => $percentages['converts'],
            ],
            'guests' => [
                'period1' => $stats1['guests'],
                'period2' => $stats2['guests'],
                'change' => $percentages['guests'],
            ],
        ];

        return $result;
    }

    /**
     * Get event reports with filtering.
     */
    public function getEventReports(?int $branchId = null, array $filters = [], int $perPage = 20): array
    {
        $baseQuery = EventReport::with(['event', 'reporter'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', function ($eq) use ($branchId) {
                    $eq->where('branch_id', $branchId);
                });
            });

        // Apply filters
        if (!empty($filters['event_type'])) {
            $baseQuery->where('event_type', $filters['event_type']);
        }

        // Apply date filters - prioritize explicit dates over period
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            // Use explicit date range
            $baseQuery->whereBetween('report_date', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date_from'])) {
            // Only start date provided
            $baseQuery->where('report_date', '>=', $filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            // Only end date provided
            $baseQuery->where('report_date', '<=', $filters['date_to']);
        } elseif (!empty($filters['period'])) {
            // Use period-based date range only if no explicit dates provided
            $dateRange = $this->getDateRange($filters['period']);
            $baseQuery->whereBetween('report_date', [$dateRange['start'], $dateRange['end']]);
        }

        // Get all matching records for summary calculation (before pagination)
        $allMatchingReports = $baseQuery->get();

        // Create a separate query for pagination
        $paginationQuery = clone $baseQuery;

        return [
            'reports' => $paginationQuery->orderBy('report_date', 'desc')->paginate($perPage),
            'summary' => $this->getReportsSummary($allMatchingReports),
            'highest_attendance' => $this->getHighestAttendanceEvent($allMatchingReports),
        ];
    }

    /**
     * Get monthly insights for a branch.
     */
    public function getMonthlyInsights(?int $branchId = null, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $query = EventReport::with('event')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', function ($eq) use ($branchId) {
                    $eq->where('branch_id', $branchId);
                });
            });

        $reports = $query->get();

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
            ],
            'totals' => $this->calculateMonthlyTotals($reports),
            'averages' => $this->calculateMonthlyAverages($reports),
            'highest_attendance_event' => $this->getHighestAttendanceEvent($reports),
            'sunday_services' => $this->getSundayServiceStats($reports),
            'event_breakdown' => $this->getEventTypeBreakdown($reports),
            'weekly_trends' => $this->getWeeklyTrends($reports, $startDate, $endDate),
        ];
    }

    /**
     * Create an event report.
     */
    public function createEventReport(array $data): EventReport
    {
        // Validate event type
        if (isset($data['event_type']) && !in_array($data['event_type'], EventReport::EVENT_TYPES)) {
            throw new \InvalidArgumentException('Invalid event type provided.');
        }

        // Ensure required numeric fields have default values to prevent null constraint violations
        $data['attendance_male'] = $data['attendance_male'] ?: 0;
        $data['attendance_female'] = $data['attendance_female'] ?: 0;
        $data['attendance_children'] = $data['attendance_children'] ?: 0;
        $data['attendance_online'] = $data['attendance_online'] ?: 0;
        $data['first_time_guests'] = $data['first_time_guests'] ?: 0;
        $data['converts'] = $data['converts'] ?: 0;
        $data['number_of_cars'] = $data['number_of_cars'] ?: 0;
        $data['is_multi_service'] = $data['is_multi_service'] ?: false;

        // Set second service defaults if multi-service
        if ($data['is_multi_service']) {
            $data['second_service_attendance_male'] = $data['second_service_attendance_male'] ?: 0;
            $data['second_service_attendance_female'] = $data['second_service_attendance_female'] ?: 0;
            $data['second_service_attendance_children'] = $data['second_service_attendance_children'] ?: 0;
            $data['second_service_first_time_guests'] = $data['second_service_first_time_guests'] ?: 0;
            $data['second_service_converts'] = $data['second_service_converts'] ?: 0;
            $data['second_service_number_of_cars'] = $data['second_service_number_of_cars'] ?: 0;
        }

        return EventReport::create($data);
    }

    /**
     * Update an event report.
     */
    public function updateEventReport(EventReport $report, array $data): EventReport
    {
        // Validate event type
        if (isset($data['event_type']) && !in_array($data['event_type'], EventReport::EVENT_TYPES)) {
            throw new \InvalidArgumentException('Invalid event type provided.');
        }

        $report->update($data);
        
        // Clear related cache
        $this->clearCacheForBranch($report->event->branch_id ?? null);
        
        return $report;
    }

    /**
     * Get member statistics.
     */
    private function getMemberStatistics(?int $branchId, array $dateRange): array
    {
        $query = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        return [
            'total' => $query->count(),
            'active' => $query->whereIn('member_status', ['member', 'volunteer', 'leader', 'minister'])->count(),
            'new_this_period' => $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'by_gender' => [
                'male' => $query->where('gender', 'male')->count(),
                'female' => $query->where('gender', 'female')->count(),
            ],
        ];
    }

    /**
     * Get event statistics.
     */
    private function getEventStatistics(?int $branchId, array $dateRange): array
    {
        $query = Event::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        return [
            'total_events' => $query->count(),
            'by_status' => [
                'published' => $query->where('status', 'published')->count(),
                'draft' => $query->where('status', 'draft')->count(),
                'cancelled' => $query->where('status', 'cancelled')->count(),
            ],
            'with_reports' => $query->whereHas('reports')->count(),
        ];
    }

    /**
     * Get attendance statistics from event reports.
     */
    private function getAttendanceStatistics(?int $branchId, array $dateRange): array
    {
        try {
            $query = EventReport::query()
                ->when($branchId, function ($q) use ($branchId) {
                    $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
                })
                ->where('event_type', 'Sunday Service')  // Only include Sunday Service events for stats cards
                ->whereBetween('report_date', [$dateRange['start'], $dateRange['end']]);

            // Use database aggregation for better performance
            $aggregated = $query->selectRaw('
                COUNT(*) as report_count,
                SUM(attendance_male + COALESCE(second_service_attendance_male, 0)) as total_male,
                SUM(attendance_female + COALESCE(second_service_attendance_female, 0)) as total_female,
                SUM(attendance_children + COALESCE(second_service_attendance_children, 0)) as total_children,
                SUM(COALESCE(attendance_online, 0) + COALESCE(second_service_attendance_online, 0)) as total_online,
                SUM(attendance_male + attendance_female + attendance_children + 
                    COALESCE(second_service_attendance_male, 0) + 
                    COALESCE(second_service_attendance_female, 0) + 
                    COALESCE(second_service_attendance_children, 0)) as total_attendance,
                SUM(COALESCE(first_time_guests, 0) + COALESCE(second_service_first_time_guests, 0)) as total_guests,
                SUM(COALESCE(converts, 0) + COALESCE(second_service_converts, 0)) as total_converts,
                AVG(attendance_male + attendance_female + attendance_children + 
                    COALESCE(second_service_attendance_male, 0) + 
                    COALESCE(second_service_attendance_female, 0) + 
                    COALESCE(second_service_attendance_children, 0)) as avg_attendance
            ')->first();

            // Sunday Service specific aggregation
            $sundayStats = $query->where('event_type', 'Sunday Service')
                ->selectRaw('AVG(attendance_male + attendance_female + attendance_children + 
                    COALESCE(second_service_attendance_male, 0) + 
                    COALESCE(second_service_attendance_female, 0) + 
                    COALESCE(second_service_attendance_children, 0)) as sunday_avg')
                ->first();

            $reportCount = (int) ($aggregated->report_count ?? 0);
            $totalAttendance = (int) ($aggregated->total_attendance ?? 0);
            $totalMale = (int) ($aggregated->total_male ?? 0);
            $totalFemale = (int) ($aggregated->total_female ?? 0);
            $totalChildren = (int) ($aggregated->total_children ?? 0);
            $totalOnline = (int) ($aggregated->total_online ?? 0);
            
            // Ensure numeric values for averages
            $avgAttendance = (float) ($aggregated->avg_attendance ?? 0);
            $sundayAvg = (float) ($sundayStats->sunday_avg ?? 0);

            return [
                'total_attendance' => $totalAttendance,
                'total_first_time_guests' => (int) ($aggregated->total_guests ?? 0),
                'total_converts' => (int) ($aggregated->total_converts ?? 0),
                'average_attendance' => round($avgAttendance, 2),
                'sunday_service_average' => round($sundayAvg, 2),
                'totals_by_gender' => [
                    'male' => $totalMale,
                    'female' => $totalFemale,
                    'children' => $totalChildren,
                    'online' => $totalOnline,
                ],
                'percentages_by_gender' => [
                    'male' => $totalAttendance > 0 ? round(($totalMale / $totalAttendance) * 100, 1) : 0,
                    'female' => $totalAttendance > 0 ? round(($totalFemale / $totalAttendance) * 100, 1) : 0,
                    'children' => $totalAttendance > 0 ? round(($totalChildren / $totalAttendance) * 100, 1) : 0,
                    'online' => $totalAttendance > 0 ? round(($totalOnline / $totalAttendance) * 100, 1) : 0,
                ],
                'average_by_gender' => [
                    'male' => $reportCount > 0 ? round(($totalMale) / $reportCount, 2) : 0,
                    'female' => $reportCount > 0 ? round(($totalFemale) / $reportCount, 2) : 0,
                    'children' => $reportCount > 0 ? round(($totalChildren) / $reportCount, 2) : 0,
                    'online' => $reportCount > 0 ? round(($totalOnline) / $reportCount, 2) : 0,
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating attendance statistics', [
                'branch_id' => $branchId,
                'date_range' => $dateRange,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return default structure to prevent frontend errors
            return [
                'total_attendance' => 0,
                'total_first_time_guests' => 0,
                'total_converts' => 0,
                'average_attendance' => 0,
                'sunday_service_average' => 0,
                'average_by_gender' => [
                    'male' => 0,
                    'female' => 0,
                    'children' => 0,
                ],
            ];
        }
    }

    /**
     * Get small group statistics.
     */
    private function getSmallGroupStatistics(?int $branchId, array $dateRange): array
    {
        try {
            // Use database aggregation for better performance
            $groupStats = SmallGroup::query()
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->selectRaw('
                    COUNT(*) as total_groups,
                    COUNT(CASE WHEN status = "active" THEN 1 END) as active_groups
                ')
                ->first();

            // Get total membership using join aggregation
            $membershipCount = DB::table('small_groups')
                ->join('member_small_groups', 'small_groups.id', '=', 'member_small_groups.small_group_id')
                ->when($branchId, fn($q) => $q->where('small_groups.branch_id', $branchId))
                ->distinct('member_small_groups.member_id')
                ->count();

            // Get meeting reports statistics
            $meetingStats = SmallGroupMeetingReport::query()
                ->when($branchId, function ($q) use ($branchId) {
                    $q->whereHas('smallGroup', fn($sg) => $sg->where('branch_id', $branchId));
                })
                ->whereBetween('meeting_date', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('AVG(total_attendance) as avg_attendance')
                ->first();

            return [
                'total_groups' => (int) ($groupStats->total_groups ?? 0),
                'total_membership' => (int) $membershipCount,
                'monthly_average_attendance' => round((float) ($meetingStats->avg_attendance ?? 0), 2),
                'active_groups' => (int) ($groupStats->active_groups ?? 0),
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating small group statistics', [
                'branch_id' => $branchId,
                'date_range' => $dateRange,
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_groups' => 0,
                'total_membership' => 0,
                'monthly_average_attendance' => 0,
                'active_groups' => 0,
            ];
        }
    }

    /**
     * Get leadership statistics.
     */
    private function getLeadershipStatistics(?int $branchId, array $dateRange): array
    {
        try {
            // Use single aggregated query for ministry and department leaders
            $leaderStats = DB::table('ministries')
                ->leftJoin('departments', 'ministries.id', '=', 'departments.ministry_id')
                ->when($branchId, fn($q) => $q->where('ministries.branch_id', $branchId))
                ->selectRaw('
                    COUNT(CASE WHEN ministries.leader_id IS NOT NULL THEN 1 END) as ministry_leaders,
                    COUNT(CASE WHEN departments.leader_id IS NOT NULL THEN 1 END) as department_leaders
                ')
                ->first();

            // Get volunteers count with optimized query
            $volunteers = DB::table('member_departments')
                ->join('departments', 'member_departments.department_id', '=', 'departments.id')
                ->join('ministries', 'departments.ministry_id', '=', 'ministries.id')
                ->when($branchId, fn($q) => $q->where('ministries.branch_id', $branchId))
                ->distinct('member_departments.member_id')
                ->count();

            $ministryLeaders = (int) ($leaderStats->ministry_leaders ?? 0);
            $departmentLeaders = (int) ($leaderStats->department_leaders ?? 0);

            return [
                'ministry_leaders' => $ministryLeaders,
                'department_leaders' => $departmentLeaders,
                'total_leaders' => $ministryLeaders + $departmentLeaders,
                'total_volunteers' => (int) $volunteers,
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating leadership statistics', [
                'branch_id' => $branchId,
                'date_range' => $dateRange,
                'error' => $e->getMessage()
            ]);
            
            return [
                'ministry_leaders' => 0,
                'department_leaders' => 0,
                'total_leaders' => 0,
                'total_volunteers' => 0,
            ];
        }
    }

    /**
     * Get TECi statistics.
     */
    private function getTeciStatistics(?int $branchId, array $dateRange): array
    {
        // This would need to be implemented based on how TECi data is stored
        // For now, returning placeholder
        return [
            'enrolled_students' => 0,
            'completed_courses' => 0,
            'active_courses' => 0,
        ];
    }

    /**
     * Get trend data for charts.
     */
    private function getTrendData(?int $branchId, array $dateRange): array
    {
        try {
            // Get attendance data grouped by event type for bar chart
            $eventTypeData = DB::table('event_reports')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->join('events', 'event_reports.event_id', '=', 'events.id')
                      ->where('events.branch_id', $branchId);
                })
                ->whereBetween('report_date', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('
                    event_type,
                    SUM(attendance_male + attendance_female + attendance_children + 
                        COALESCE(second_service_attendance_male, 0) + 
                        COALESCE(second_service_attendance_female, 0) + 
                        COALESCE(second_service_attendance_children, 0) +
                        COALESCE(attendance_online, 0) +
                        COALESCE(second_service_attendance_online, 0)) as total_attendance,
                    COUNT(*) as report_count
                ')
                ->groupBy('event_type')
                ->orderBy('total_attendance', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'event_type' => $item->event_type,
                        'total_attendance' => (int) $item->total_attendance,
                        'report_count' => (int) $item->report_count,
                        'average_attendance' => $item->report_count > 0 ? round((float) $item->total_attendance / (float) $item->report_count, 1) : 0,
                    ];
                });

            // Get weekly Sunday service breakdown for the current month
            $weeklySundayBreakdown = $this->getWeeklySundayServiceBreakdown($branchId, $dateRange);

            return [
                'attendance_by_event_type' => $eventTypeData->toArray(),
                'weekly_sunday_breakdown' => $weeklySundayBreakdown,
            ];
        } catch (\Exception $e) {
            \Log::error('Error generating trend data', [
                'branch_id' => $branchId,
                'date_range' => $dateRange,
                'error' => $e->getMessage()
            ]);
            
            return [
                'attendance_by_event_type' => [],
                'weekly_sunday_breakdown' => [],
            ];
        }
    }

    /**
     * Get weekly Sunday service breakdown for charts.
     */
    private function getWeeklySundayServiceBreakdown(?int $branchId, array $dateRange): array
    {
        try {
            $startDate = Carbon::parse($dateRange['start']);
            $endDate = Carbon::parse($dateRange['end']);
            
            // Generate all weeks in the date range first to ensure proper ordering
            $allWeeks = [];
            $currentWeekStart = $startDate->copy()->startOfWeek();
            $weekNumber = 1;
            
            while ($currentWeekStart->lte($endDate)) {
                $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
                
                // Ensure we don't go beyond the end date
                if ($currentWeekEnd->gt($endDate)) {
                    $currentWeekEnd = $endDate->copy();
                }
                
                $allWeeks[] = [
                    'week_start' => $currentWeekStart->copy(),
                    'week_end' => $currentWeekEnd->copy(),
                    'week_number' => $weekNumber,
                    'label' => 'Week ' . $weekNumber,
                    'date_label' => $currentWeekStart->format('M j') . ' - ' . $currentWeekEnd->format('j'),
                ];
                
                $currentWeekStart->addWeek();
                $weekNumber++;
            }
            
            // Get actual attendance data from database
            $weeklyReports = DB::table('event_reports')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->join('events', 'event_reports.event_id', '=', 'events.id')
                      ->where('events.branch_id', $branchId);
                })
                ->where('event_type', 'Sunday Service')
                ->whereBetween('report_date', [$startDate, $endDate])
                ->selectRaw('
                    report_date,
                    SUM(attendance_male + attendance_female + attendance_children + 
                        COALESCE(second_service_attendance_male, 0) + 
                        COALESCE(second_service_attendance_female, 0) + 
                        COALESCE(second_service_attendance_children, 0) +
                        COALESCE(attendance_online, 0) +
                        COALESCE(second_service_attendance_online, 0)) as total_attendance,
                    COUNT(*) as reports_count
                ')
                ->groupBy('report_date')
                ->get();

            // Map attendance data to weeks
            $weeklyData = [];
            foreach ($allWeeks as $week) {
                $weekAttendance = 0;
                $weekReportsCount = 0;
                
                // Find reports that fall within this week
                foreach ($weeklyReports as $report) {
                    $reportDate = Carbon::parse($report->report_date);
                    if ($reportDate->between($week['week_start'], $week['week_end'])) {
                        $weekAttendance += (int) $report->total_attendance;
                        $weekReportsCount += (int) $report->reports_count;
                    }
                }
                
                $weeklyData[] = [
                    'week' => $week['label'],
                    'label' => $week['date_label'],
                    'attendance' => $weekAttendance,
                    'reports_count' => $weekReportsCount,
                    'week_start' => $week['week_start']->format('Y-m-d'),
                    'week_end' => $week['week_end']->format('Y-m-d'),
                ];
            }

            return $weeklyData;
        } catch (\Exception $e) {
            \Log::error('Error generating weekly Sunday service breakdown', [
                'branch_id' => $branchId,
                'date_range' => $dateRange,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get trend data for a specific date range (public method for controller).
     */
    public function getTrendDataForRange(?int $branchId, array $dateRange): array
    {
        return $this->getTrendData($branchId, $dateRange);
    }

    /**
     * Generate Global Ministry Monthly Report for a specific branch and month.
     */
    public function getGlobalMinistryMonthlyReport(?int $branchId, int $year, int $month): array
    {
        $cacheKey = "global_ministry_report_{$branchId}_{$year}_{$month}";
        $cacheTags = ['global_ministry_reports', "branch_{$branchId}", 'event_reports', 'members'];
        
        return Cache::remember($cacheKey, 3600, function () use ($branchId, $year, $month) {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            
            return [
                'report_info' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $startDate->format('F'),
                    'branch_id' => $branchId,
                    'generated_at' => now(),
                ],
                'sunday_service_attendance' => $this->getSundayServiceMonthlyAverage($branchId, $startDate, $endDate),
                'guest_attraction' => $this->getGuestAttractionData($branchId, $startDate, $endDate),
                'converts' => $this->getConvertsData($branchId, $startDate, $endDate),
                'converts_assimilated_fdc_graduates' => $this->getConvertsAssimilatedFdcGraduates($branchId, $startDate, $endDate),
                'membership_class_graduates' => $this->getMembershipClassGraduates($branchId, $startDate, $endDate),
                'teci_graduates' => $this->getTeciGraduates($branchId, $startDate, $endDate),
                'small_groups' => $this->getSmallGroupsData($branchId, $startDate, $endDate),
                'g_squad_volunteers' => $this->getGSquadVolunteers($branchId),
                'leadership' => $this->getLeadershipData($branchId),
                'baptisms' => $this->getBaptismsData($branchId, $startDate, $endDate),
                'teci_enrollment' => $this->getTeciEnrollmentData($branchId),
            ];
        });
    }

    /**
     * Generate Global Ministry Monthly Report for all branches (Admin view).
     */
    public function getAllBranchesGlobalMinistryReport(int $year, int $month): array
    {
        $cacheKey = "global_ministry_report_all_branches_{$year}_{$month}";
        $cacheTags = ['global_ministry_reports', 'all_branches', 'event_reports', 'members'];
        
        return Cache::remember($cacheKey, 3600, function () use ($year, $month) {
            // Get all branches
            $branches = \App\Models\Branch::all();
            
            $branchReports = [];
            $totals = [
                'sunday_service_attendance' => 0,
                'guest_attraction' => 0,
                'converts' => 0,
                'converts_assimilated_fdc_graduates' => 0,
                'membership_class_graduates' => 0,
                'teci_graduates' => 0,
                'small_groups_count' => 0,
                'small_groups_membership' => 0,
                'small_groups_avg_attendance' => 0,
                'g_squad_volunteers' => 0,
                'total_leaders' => 0,
                'baby_dedication' => 0,
                'water_baptism' => 0,
                'holy_ghost_baptism' => 0,
                'teci_current_enrollment' => 0,
                'teci_pending_graduation' => 0,
            ];
            
            foreach ($branches as $branch) {
                $branchReport = $this->getGlobalMinistryMonthlyReport($branch->id, $year, $month);
                $branchReport['branch_name'] = $branch->name;
                $branchReports[] = $branchReport;
                
                // Accumulate totals
                $totals['sunday_service_attendance'] += $branchReport['sunday_service_attendance']['monthly_average'];
                $totals['guest_attraction'] += $branchReport['guest_attraction']['total_guests'];
                $totals['converts'] += $branchReport['converts']['total_converts'];
                $totals['converts_assimilated_fdc_graduates'] += $branchReport['converts_assimilated_fdc_graduates']['count'];
                $totals['membership_class_graduates'] += $branchReport['membership_class_graduates']['count'];
                $totals['teci_graduates'] += $branchReport['teci_graduates']['count'];
                $totals['small_groups_count'] += $branchReport['small_groups']['total_groups'];
                $totals['small_groups_membership'] += $branchReport['small_groups']['total_membership'];
                $totals['small_groups_avg_attendance'] += $branchReport['small_groups']['monthly_average_attendance'];
                $totals['g_squad_volunteers'] += $branchReport['g_squad_volunteers']['count'];
                $totals['total_leaders'] += $branchReport['leadership']['total_leaders'];
                $totals['baby_dedication'] += $branchReport['baptisms']['baby_dedication'];
                $totals['water_baptism'] += $branchReport['baptisms']['water_baptism'];
                $totals['holy_ghost_baptism'] += $branchReport['baptisms']['holy_ghost_baptism'];
                $totals['teci_current_enrollment'] += $branchReport['teci_enrollment']['current_enrollment'];
                $totals['teci_pending_graduation'] += $branchReport['teci_enrollment']['pending_graduation'];
            }
            
            return [
                'report_info' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => Carbon::create($year, $month, 1)->format('F'),
                    'generated_at' => now(),
                    'total_branches' => $branches->count(),
                ],
                'branch_reports' => $branchReports,
                'organization_totals' => $totals,
            ];
        });
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange(string $period): array
    {
        $now = now();
        
        return match ($period) {
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get period statistics for comparison.
     */
    private function getPeriodStatistics(?int $branchId, array $period): array
    {
        $query = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->whereBetween('report_date', [$period['start'], $period['end']]);

        $reports = $query->get();

        return [
            'attendance' => $reports->sum('combined_total_attendance'),
            'guests' => $reports->sum('combined_first_time_guests'),
            'converts' => $reports->sum('combined_converts'),
            'events_count' => $reports->count(),
            'sunday_service_avg' => $reports->where('event_type', 'Sunday Service')->avg('combined_total_attendance') ?? 0,
        ];
    }

    /**
     * Calculate changes between periods.
     */
    private function calculateChanges(array $stats1, array $stats2): array
    {
        return [
            'attendance' => $stats2['attendance'] - $stats1['attendance'],
            'guests' => $stats2['guests'] - $stats1['guests'],
            'converts' => $stats2['converts'] - $stats1['converts'],
            'events_count' => $stats2['events_count'] - $stats1['events_count'],
            'sunday_service_avg' => $stats2['sunday_service_avg'] - $stats1['sunday_service_avg'],
        ];
    }

    /**
     * Calculate percentage changes between periods.
     */
    private function calculatePercentageChanges(array $stats1, array $stats2): array
    {
        $calculate = function ($old, $new) {
            if ($old == 0) return $new > 0 ? 100 : 0;
            return round((($new - $old) / $old) * 100, 2);
        };

        return [
            'attendance' => $calculate($stats1['attendance'], $stats2['attendance']),
            'guests' => $calculate($stats1['guests'], $stats2['guests']),
            'converts' => $calculate($stats1['converts'], $stats2['converts']),
            'events_count' => $calculate($stats1['events_count'], $stats2['events_count']),
            'sunday_service_avg' => $calculate($stats1['sunday_service_avg'], $stats2['sunday_service_avg']),
        ];
    }

    /**
     * Get reports summary.
     */
    private function getReportsSummary($reports): array
    {
        $reportCount = $reports->count();
        
        // Calculate gender totals
        $totalMale = $reports->sum(function ($report) {
            $genderTotals = $report->combined_totals_by_gender;
            return $genderTotals['male'] ?? 0;
        });
        
        $totalFemale = $reports->sum(function ($report) {
            $genderTotals = $report->combined_totals_by_gender;
            return $genderTotals['female'] ?? 0;
        });
        
        $totalChildren = $reports->sum(function ($report) {
            $genderTotals = $report->combined_totals_by_gender;
            return $genderTotals['children'] ?? 0;
        });

        $totalOnline = $reports->sum(function ($report) {
            $genderTotals = $report->combined_totals_by_gender;
            return $genderTotals['online'] ?? 0;
        });

        return [
            'total_events' => $reportCount,
            'total_attendance' => $reports->sum('combined_total_attendance'),
            'total_first_time_guests' => $reports->sum('combined_first_time_guests'),
            'total_converts' => $reports->sum('combined_converts'),
            'average_attendance' => $reports->avg('combined_total_attendance'),
            'average_male' => $reportCount > 0 ? $totalMale / $reportCount : 0,
            'average_female' => $reportCount > 0 ? $totalFemale / $reportCount : 0,
            'average_children' => $reportCount > 0 ? $totalChildren / $reportCount : 0,
            'average_online' => $reportCount > 0 ? $totalOnline / $reportCount : 0,
            'total_male' => $totalMale,
            'total_female' => $totalFemale,
            'total_children' => $totalChildren,
            'total_online' => $totalOnline,
        ];
    }

    /**
     * Get highest attendance event.
     */
    private function getHighestAttendanceEvent($reports): ?array
    {
        if ($reports->isEmpty()) {
            return null;
        }

        // Since combined_total_attendance is an accessor, we need to load the data first
        // and then sort in PHP rather than in the database
        $highest = $reports->sortByDesc(function ($report) {
            return $report->combined_total_attendance;
        })->first();
        
        if (!$highest) return null;

        // Use the correct event field (name instead of title)
        $eventName = $highest->event->name ?? 'Unknown Event';
        
        // For recurring events or if name is generic, create a more descriptive name
        if ($eventName === 'Sunday Service' || $eventName === 'Midweek Service') {
            // Use event_type if it's different from the base name, otherwise add date context
            if ($highest->event_type && $highest->event_type !== $eventName) {
                $eventName = $highest->event_type;
            } else {
                // Add date context for better identification
                $date = $highest->report_date;
                $eventName = $eventName . ' (' . $date->format('M j, Y') . ')';
            }
        }

        return [
            'event_name' => $eventName,
            'event_type' => $highest->event_type,
            'date' => $highest->report_date,
            'attendance' => $highest->combined_total_attendance,
        ];
    }

    /**
     * Get highest attendance event for a specific period.
     */
    private function getHighestAttendanceEventForPeriod(?int $branchId, array $period): ?array
    {
        $query = EventReport::with('event')
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->whereBetween('report_date', [$period['start'], $period['end']]);

        $reports = $query->get();
        
        return $this->getHighestAttendanceEvent($reports);
    }

    /**
     * Calculate monthly totals.
     */
    private function calculateMonthlyTotals($reports): array
    {
        $totals = $reports->reduce(function ($carry, $report) {
            $gender = $report->combined_totals_by_gender;
            return [
                'attendance' => $carry['attendance'] + $report->combined_total_attendance,
                'male' => $carry['male'] + $gender['male'],
                'female' => $carry['female'] + $gender['female'],
                'children' => $carry['children'] + $gender['children'],
                'guests' => $carry['guests'] + $report->combined_first_time_guests,
                'converts' => $carry['converts'] + $report->combined_converts,
            ];
        }, ['attendance' => 0, 'male' => 0, 'female' => 0, 'children' => 0, 'guests' => 0, 'converts' => 0]);

        return $totals;
    }

    /**
     * Calculate monthly averages.
     */
    private function calculateMonthlyAverages($reports): array
    {
        $count = $reports->count();
        if ($count === 0) return ['attendance' => 0, 'male' => 0, 'female' => 0, 'children' => 0];

        $totals = $this->calculateMonthlyTotals($reports);
        
        return [
            'attendance' => round($totals['attendance'] / $count, 2),
            'male' => round($totals['male'] / $count, 2),
            'female' => round($totals['female'] / $count, 2),
            'children' => round($totals['children'] / $count, 2),
        ];
    }

    /**
     * Get Sunday service specific statistics.
     */
    private function getSundayServiceStats($reports): array
    {
        $sundayReports = $reports->where('event_type', 'Sunday Service');
        
        return [
            'count' => $sundayReports->count(),
            'average_attendance' => $sundayReports->avg('combined_total_attendance'),
            'total_attendance' => $sundayReports->sum('combined_total_attendance'),
        ];
    }

    /**
     * Get event type breakdown.
     */
    private function getEventTypeBreakdown($reports): array
    {
        return $reports->groupBy('event_type')->map(function ($typeReports, $type) {
            return [
                'type' => $type,
                'count' => $typeReports->count(),
                'total_attendance' => $typeReports->sum('combined_total_attendance'),
                'average_attendance' => $typeReports->avg('combined_total_attendance'),
            ];
        })->values()->toArray();
    }

    /**
     * Get weekly trends within a month.
     */
    private function getWeeklyTrends($reports, Carbon $startDate, Carbon $endDate): array
    {
        $weeks = [];
        $current = $startDate->copy()->startOfWeek();
        
        while ($current->lte($endDate)) {
            $weekEnd = $current->copy()->endOfWeek();
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }
            
            $weekReports = $reports->whereBetween('report_date', [$current->format('Y-m-d'), $weekEnd->format('Y-m-d')]);
            
            $weeks[] = [
                'week_start' => $current->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'total_attendance' => $weekReports->sum('combined_total_attendance'),
                'events_count' => $weekReports->count(),
                'average_attendance' => $weekReports->avg('combined_total_attendance') ?? 0,
            ];
            
            $current->addWeek();
        }
        
        return $weeks;
    }

    /**
     * Clear cache for a specific branch.
     */
    private function clearCacheForBranch(?int $branchId): void
    {
        try {
            // Clear specific cache keys for the branch
            $periods = ['week', 'month', 'quarter', 'year'];
            
            foreach ($periods as $period) {
                Cache::forget("dashboard_stats_{$branchId}_{$period}");
            }
            
            // Clear global ministry report cache keys
            $currentYear = now()->year;
            for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    Cache::forget("global_ministry_report_{$branchId}_{$year}_{$month}");
                }
            }
            
            // Clear all branches report cache
            for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    Cache::forget("all_branches_global_ministry_report_{$year}_{$month}");
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning('Cache clearing failed for branch', [
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Sunday Service monthly average attendance.
     */
    private function getSundayServiceMonthlyAverage(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $query = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->where('event_type', 'Sunday Service')
            ->whereBetween('report_date', [$startDate, $endDate]);

        $totalAttendance = $query->sum(DB::raw('
            attendance_male + attendance_female + attendance_children + 
            COALESCE(second_service_attendance_male, 0) + 
            COALESCE(second_service_attendance_female, 0) + 
            COALESCE(second_service_attendance_children, 0)
        '));

        $serviceCount = $query->count();
        $monthlyAverage = $serviceCount > 0 ? round($totalAttendance / $serviceCount, 2) : 0;

        return [
            'monthly_average' => $monthlyAverage,
            'total_attendance' => $totalAttendance,
            'service_count' => $serviceCount,
        ];
    }

    /**
     * Get guest attraction data.
     */
    private function getGuestAttractionData(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $totalGuests = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->whereBetween('report_date', [$startDate, $endDate])
            ->sum(DB::raw('first_time_guests + COALESCE(second_service_first_time_guests, 0)'));

        return [
            'total_guests' => $totalGuests,
        ];
    }

    /**
     * Get converts data.
     */
    private function getConvertsData(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $totalConverts = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->whereBetween('report_date', [$startDate, $endDate])
            ->sum(DB::raw('converts + COALESCE(second_service_converts, 0)'));

        return [
            'total_converts' => $totalConverts,
        ];
    }

    /**
     * Get converts assimilated/FDC graduates.
     */
    private function getConvertsAssimilatedFdcGraduates(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        // Count members who attended membership class during this period
        $count = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('date_attended_membership_class', [$startDate, $endDate])
            ->whereNotNull('date_attended_membership_class')
            ->count();

        return [
            'count' => $count,
        ];
    }

    /**
     * Get membership class graduates.
     */
    private function getMembershipClassGraduates(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        // Count members who completed membership class and became active members during this period
        $count = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('date_attended_membership_class', [$startDate, $endDate])
            ->whereNotNull('date_attended_membership_class')
            ->where('member_status', '!=', 'visitor')
            ->count();

        return [
            'count' => $count,
        ];
    }

    /**
     * Get TECi graduates.
     */
    private function getTeciGraduates(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        // Count members who graduated from TECi during this period
        $count = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('teci_status', 'graduated')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        return [
            'count' => $count,
        ];
    }

    /**
     * Get small groups data.
     */
    private function getSmallGroupsData(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $smallGroupsQuery = SmallGroup::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active');

        $totalGroups = $smallGroupsQuery->count();
        $totalMembership = $smallGroupsQuery->withCount('members')->get()->sum('members_count');

        $avgAttendance = SmallGroupMeetingReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('smallGroup', fn($sg) => $sg->where('branch_id', $branchId));
            })
            ->whereBetween('meeting_date', [$startDate, $endDate])
            ->avg('total_attendance') ?? 0;

        return [
            'total_groups' => $totalGroups,
            'total_membership' => $totalMembership,
            'monthly_average_attendance' => round($avgAttendance, 2),
        ];
    }

    /**
     * Get G-squad (volunteers) data.
     */
    private function getGSquadVolunteers(?int $branchId): array
    {
        $count = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('member_status', ['volunteer', 'leader', 'minister'])
            ->count();

        return [
            'count' => $count,
        ];
    }

    /**
     * Get leadership data.
     */
    private function getLeadershipData(?int $branchId): array
    {
        $totalLeaders = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('member_status', ['leader', 'minister'])
            ->count();

        return [
            'total_leaders' => $totalLeaders,
        ];
    }

    /**
     * Get baptisms data.
     */
    private function getBaptismsData(?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        // Count baptism events during the period
        $babyDedication = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->where('event_type', 'Baby Dedication')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->sum(DB::raw('attendance_male + attendance_female + attendance_children'));

        $waterBaptism = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->where('event_type', 'Water Baptism')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->sum(DB::raw('attendance_male + attendance_female + attendance_children'));

        $holyGhostBaptism = EventReport::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('event', fn($eq) => $eq->where('branch_id', $branchId));
            })
            ->where('event_type', 'Holy Ghost Baptism')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->sum(DB::raw('attendance_male + attendance_female + attendance_children'));

        return [
            'baby_dedication' => $babyDedication,
            'water_baptism' => $waterBaptism,
            'holy_ghost_baptism' => $holyGhostBaptism,
        ];
    }

    /**
     * Get TECi enrollment data.
     */
    private function getTeciEnrollmentData(?int $branchId): array
    {
        $currentEnrollment = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('teci_status', 'enrolled')
            ->count();

        $pendingGraduation = Member::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('teci_status', ['enrolled', 'in_progress'])
            ->count();

        return [
            'current_enrollment' => $currentEnrollment,
            'pending_graduation' => $pendingGraduation,
        ];
    }
} 