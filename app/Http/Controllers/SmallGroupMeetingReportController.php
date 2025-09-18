<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SmallGroupMeetingReportRequest;
use App\Models\SmallGroup;
use App\Models\SmallGroupMeetingReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class SmallGroupMeetingReportController extends Controller
{
    /**
     * Display a listing of meeting reports.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = SmallGroupMeetingReport::with(['smallGroup.branch', 'reportedBy'])
            ->orderBy('meeting_date', 'desc');

        // Apply role-based filtering
        if ($user->isSuperAdmin()) {
            // Super admin can see all reports, optionally filter by branch
            if ($request->has('branch_id')) {
                $query->forBranch($request->branch_id);
            }
        } elseif ($user->isBranchPastor()) {
            // Branch pastor can see reports for their branch
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch) {
                $query->forBranch($userBranch->id);
            }
        } else {
            // Small group leaders can only see reports for their groups
            $userGroups = SmallGroup::where('leader_id', $user->member?->id)->pluck('id');
            $query->whereIn('small_group_id', $userGroups);
        }

        // Apply filters
        if ($request->has('small_group_id')) {
            $query->forSmallGroup($request->small_group_id);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        $reports = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created meeting report.
     */
    public function store(SmallGroupMeetingReportRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user can create reports for this small group
        $smallGroup = SmallGroup::findOrFail($request->small_group_id);
        
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            // Check if user is the leader of this small group
            if ($smallGroup->leader_id !== $user->member?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create reports for small groups you lead.',
                ], 403);
            }
        }

        // Check for duplicate reports on the same date
        $existingReport = SmallGroupMeetingReport::where('small_group_id', $request->small_group_id)
            ->where('meeting_date', $request->meeting_date)
            ->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'A report for this date already exists.',
            ], 422);
        }

        $reportData = $request->validated();
        $reportData['reported_by'] = $user->id;
        $reportData['submitted_at'] = now();

        $report = SmallGroupMeetingReport::create($reportData);
        $report->load(['smallGroup', 'reportedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Meeting report created successfully.',
            'data' => $report,
        ], 201);
    }

    /**
     * Display the specified meeting report.
     */
    public function show(SmallGroupMeetingReport $report): JsonResponse
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            if ($report->smallGroup->leader_id !== $user->member?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this report.',
                ], 403);
            }
        }

        $report->load(['smallGroup', 'reportedBy', 'approvedBy']);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update the specified meeting report.
     */
    public function update(SmallGroupMeetingReportRequest $request, SmallGroupMeetingReport $report): JsonResponse
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            if ($report->smallGroup->leader_id !== $user->member?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this report.',
                ], 403);
            }
        }

        // Check if report can be updated
        if ($report->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update an approved report.',
            ], 422);
        }

        $report->update($request->validated());
        $report->load(['smallGroup', 'reportedBy', 'approvedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Meeting report updated successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Remove the specified meeting report.
     */
    public function destroy(SmallGroupMeetingReport $report): JsonResponse
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            if ($report->smallGroup->leader_id !== $user->member?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this report.',
                ], 403);
            }
        }

        // Check if report can be deleted
        if ($report->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete an approved report.',
            ], 422);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meeting report deleted successfully.',
        ]);
    }

    /**
     * Approve a meeting report.
     */
    public function approve(SmallGroupMeetingReport $report): JsonResponse
    {
        $user = Auth::user();
        
        // Only pastors and super admins can approve reports
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve reports.',
            ], 403);
        }

        if ($report->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Report is already approved.',
            ], 422);
        }

        $report->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $report->load(['smallGroup', 'reportedBy', 'approvedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Meeting report approved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Reject a meeting report.
     */
    public function reject(Request $request, SmallGroupMeetingReport $report): JsonResponse
    {
        $user = Auth::user();
        
        // Only pastors and super admins can reject reports
        if (!$user->isSuperAdmin() && !$user->isBranchPastor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject reports.',
            ], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($report->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an approved report.',
            ], 422);
        }

        $report->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $report->load(['smallGroup', 'reportedBy', 'approvedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Meeting report rejected.',
            'data' => $report,
        ]);
    }

    /**
     * Get user's small groups for dropdown.
     */
    public function getMySmallGroups(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            // Super admins can see all groups
            $groups = SmallGroup::with('branch')->get();
        } elseif ($user->isBranchPastor()) {
            // Branch pastors can see groups in their branch
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch) {
                $groups = SmallGroup::where('branch_id', $userBranch->id)->with('branch')->get();
            } else {
                $groups = collect();
            }
        } else {
            // Small group leaders can only see their groups
            $groups = SmallGroup::where('leader_id', $user->member?->id)->with('branch')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'branch_name' => $group->branch?->name,
                    'meeting_day' => $group->meeting_day,
                    'meeting_time' => $group->meeting_time,
                ];
            }),
        ]);
    }

    /**
     * Get attendance statistics and analytics.
     */
    public function getAttendanceStatistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Validate request parameters
        $request->validate([
            'period' => 'nullable|in:week,month,quarter,year,custom,this_week,this_month,this_quarter,this_year',
            'date_filter' => 'nullable|in:week,month,quarter,year,custom,this_week,this_month,this_quarter,this_year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'small_group_id' => 'nullable|integer|exists:small_groups,id',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        // Determine status filter - default to 'approved' if not specified
        $status = $request->get('status', 'approved');
        $query = SmallGroupMeetingReport::byStatus($status);

        // Apply role-based filtering
        if ($user->isSuperAdmin()) {
            if ($request->has('branch_id')) {
                $query->forBranch($request->branch_id);
            }
        } elseif ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch) {
                $query->forBranch($userBranch->id);
            }
        } else {
            // Small group leaders can only see their groups
            $userGroups = SmallGroup::where('leader_id', $user->member?->id)->pluck('id');
            $query->whereIn('small_group_id', $userGroups);
        }

        // Apply small group filter
        if ($request->has('small_group_id')) {
            $query->forSmallGroup($request->small_group_id);
        }

        // Apply date filtering - handle both 'period' and 'date_filter' parameter names
        $period = $request->get('period') ?: $request->get('date_filter', 'month');
        $startDate = $request->get('start_date') ?: $request->get('from_date');
        $endDate = $request->get('end_date') ?: $request->get('to_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $query->inDateRange($startDate, $endDate);
        } else {
            [$startDate, $endDate] = $this->getDateRangeForPeriod($period);
            $query->inDateRange($startDate, $endDate);
        }

        // Get statistics
        $statistics = $query->selectRaw('
            COUNT(*) as total_meetings,
            COALESCE(SUM(total_attendance), 0) as total_attendance,
            COALESCE(SUM(male_attendance), 0) as total_male,
            COALESCE(SUM(female_attendance), 0) as total_female,
            COALESCE(SUM(children_attendance), 0) as total_children,
            COALESCE(SUM(first_time_guests), 0) as total_guests,
            COALESCE(SUM(converts), 0) as total_converts,
            COALESCE(AVG(total_attendance), 0) as avg_attendance,
            COUNT(DISTINCT small_group_id) as active_groups
        ')->first();

        // Get trend data (weekly breakdown)
        $trendData = $query->selectRaw('
            WEEK(meeting_date, 1) as week_number,
            YEAR(meeting_date) as year,
            DATE(DATE_SUB(meeting_date, INTERVAL WEEKDAY(meeting_date) DAY)) as week_start,
            COALESCE(SUM(total_attendance), 0) as attendance,
            COALESCE(SUM(first_time_guests), 0) as guests,
            COALESCE(SUM(converts), 0) as converts,
            COUNT(*) as meetings
        ')
        ->groupBy('year', 'week_number', 'week_start')
        ->orderBy('year')
        ->orderBy('week_number')
        ->get();

        // Get top performing groups (fix duplicate issue by separating grouping from eager loading)
        $topGroupsData = $query->selectRaw('
                small_group_id,
                COALESCE(SUM(total_attendance), 0) as total_attendance,
                COALESCE(SUM(first_time_guests), 0) as total_guests,
                COALESCE(SUM(converts), 0) as total_converts,
                COALESCE(AVG(total_attendance), 0) as avg_attendance,
                COUNT(*) as meetings_count
            ')
            ->groupBy('small_group_id')
            ->orderBy('total_attendance', 'desc')
            ->limit(10)
            ->get();

        // Load the relationships separately to avoid GROUP BY conflicts
        $topGroups = $topGroupsData->map(function ($item) {
            $smallGroup = SmallGroup::with('branch')->find($item->small_group_id);
            return (object) [
                'small_group_id' => $item->small_group_id,
                'total_attendance' => $item->total_attendance,
                'total_guests' => $item->total_guests,
                'total_converts' => $item->total_converts,
                'avg_attendance' => $item->avg_attendance,
                'meetings_count' => $item->meetings_count,
                'small_group' => $smallGroup,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                // Frontend expects these specific field names
                'total_reports' => $statistics->total_meetings,
                'avg_attendance' => $statistics->avg_attendance,
                'total_guests' => $statistics->total_guests,
                'total_converts' => $statistics->total_converts,
                'total_attendance' => $statistics->total_attendance,
                'active_groups' => $statistics->active_groups,
                // Additional data for advanced analytics
                'statistics' => $statistics,
                'trends' => $trendData,
                'top_groups' => $topGroups,
                'period' => $period,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Compare attendance between two periods.
     */
    public function compareAttendance(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $baseQuery = SmallGroupMeetingReport::byStatus('approved');

        // Apply role-based filtering
        if ($user->isSuperAdmin()) {
            if ($request->has('branch_id')) {
                $baseQuery->forBranch($request->branch_id);
            }
        } elseif ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch) {
                $baseQuery->forBranch($userBranch->id);
            }
        } else {
            $userGroups = SmallGroup::where('leader_id', $user->member?->id)->pluck('id');
            $baseQuery->whereIn('small_group_id', $userGroups);
        }

        // Get statistics for period 1
        $period1Stats = (clone $baseQuery)
            ->inDateRange($request->period1_start, $request->period1_end)
            ->selectRaw('
                COUNT(*) as total_meetings,
                COALESCE(SUM(total_attendance), 0) as total_attendance,
                COALESCE(SUM(first_time_guests), 0) as total_guests,
                COALESCE(SUM(converts), 0) as total_converts,
                COALESCE(AVG(total_attendance), 0) as avg_attendance
            ')
            ->first();

        // Get statistics for period 2
        $period2Stats = (clone $baseQuery)
            ->inDateRange($request->period2_start, $request->period2_end)
            ->selectRaw('
                COUNT(*) as total_meetings,
                COALESCE(SUM(total_attendance), 0) as total_attendance,
                COALESCE(SUM(first_time_guests), 0) as total_guests,
                COALESCE(SUM(converts), 0) as total_converts,
                COALESCE(AVG(total_attendance), 0) as avg_attendance
            ')
            ->first();

        // Calculate percentage changes
        $comparison = [
            'attendance_change' => $this->calculatePercentageChange(
                $period1Stats->total_attendance ?? 0,
                $period2Stats->total_attendance ?? 0
            ),
            'guests_change' => $this->calculatePercentageChange(
                $period1Stats->total_guests ?? 0,
                $period2Stats->total_guests ?? 0
            ),
            'converts_change' => $this->calculatePercentageChange(
                $period1Stats->total_converts ?? 0,
                $period2Stats->total_converts ?? 0
            ),
            'meetings_change' => $this->calculatePercentageChange(
                $period1Stats->total_meetings ?? 0,
                $period2Stats->total_meetings ?? 0
            ),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'period1' => [
                    'start_date' => $request->period1_start,
                    'end_date' => $request->period1_end,
                    'statistics' => $period1Stats,
                ],
                'period2' => [
                    'start_date' => $request->period2_start,
                    'end_date' => $request->period2_end,
                    'statistics' => $period2Stats,
                ],
                'comparison' => $comparison,
            ],
        ]);
    }

    /**
     * Get date range for a given period.
     */
    private function getDateRangeForPeriod(string $period): array
    {
        $now = Carbon::now();
        
        return match ($period) {
            'week', 'this_week' => [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()],
            'month', 'this_month' => [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()],
            'quarter', 'this_quarter' => [$now->startOfQuarter()->toDateString(), $now->endOfQuarter()->toDateString()],
            'year', 'this_year' => [$now->startOfYear()->toDateString(), $now->endOfYear()->toDateString()],
            default => [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()],
        };
    }

    /**
     * Get attendance trends (alias for getAttendanceStatistics).
     */
    public function getTrends(Request $request): JsonResponse
    {
        return $this->getAttendanceStatistics($request);
    }

    /**
     * Get period comparison data.
     */
    public function getComparison(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('date_filter', 'this_week');
        
        // Get current period date range
        [$currentStart, $currentEnd] = $this->getDateRangeForPeriod($period === 'this_week' ? 'week' : $period);
        
        // Calculate previous period date range
        $duration = Carbon::parse($currentStart)->diffInDays(Carbon::parse($currentEnd));
        $previousStart = Carbon::parse($currentStart)->subDays($duration + 1)->toDateString();
        $previousEnd = Carbon::parse($currentStart)->subDay()->toDateString();
        
        // Get current period stats
        $currentStats = $this->getPeriodStats($user, $currentStart, $currentEnd, $request);
        
        // Get previous period stats
        $previousStats = $this->getPeriodStats($user, $previousStart, $previousEnd, $request);
        
        // Calculate percentage changes
        $attendanceChange = $this->calculatePercentageChange(
            $previousStats['total_attendance'] ?? 0, 
            $currentStats['total_attendance'] ?? 0
        );
        
        $guestsChange = $this->calculatePercentageChange(
            $previousStats['total_guests'] ?? 0, 
            $currentStats['total_guests'] ?? 0
        );
        
        $convertsChange = $this->calculatePercentageChange(
            $previousStats['total_converts'] ?? 0, 
            $currentStats['total_converts'] ?? 0
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'attendance_change' => $attendanceChange,
                'guests_change' => $guestsChange,
                'converts_change' => $convertsChange,
                'current_period' => $currentStats,
                'previous_period' => $previousStats,
            ],
        ]);
    }

    /**
     * Get stats for a specific period.
     */
    private function getPeriodStats($user, string $startDate, string $endDate, Request $request): array
    {
        $query = SmallGroupMeetingReport::byStatus('approved')
            ->inDateRange($startDate, $endDate);
            
        // Apply role-based filtering
        if ($user->isSuperAdmin()) {
            if ($request->has('branch_id')) {
                $query->forBranch($request->branch_id);
            }
        } elseif ($user->isBranchPastor()) {
            $userBranch = $user->getPrimaryBranch();
            if ($userBranch) {
                $query->forBranch($userBranch->id);
            }
        } else {
            $userGroups = SmallGroup::where('leader_id', $user->member?->id)->pluck('id');
            $query->whereIn('small_group_id', $userGroups);
        }
        
        $stats = $query->selectRaw('
            COALESCE(SUM(total_attendance), 0) as total_attendance,
            COALESCE(SUM(first_time_guests), 0) as total_guests,
            COALESCE(SUM(converts), 0) as total_converts,
            COUNT(*) as total_meetings
        ')->first();
        
        return [
            'total_attendance' => (int) ($stats->total_attendance ?? 0),
            'total_guests' => (int) ($stats->total_guests ?? 0),
            'total_converts' => (int) ($stats->total_converts ?? 0),
            'total_meetings' => (int) ($stats->total_meetings ?? 0),
        ];
    }

    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange($oldValue, $newValue): float
    {
        $oldValue = (float) $oldValue;
        $newValue = (float) $newValue;
        
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }
}
