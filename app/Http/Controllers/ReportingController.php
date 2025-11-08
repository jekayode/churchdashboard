<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EventReportRequest;
use App\Models\EventReport;
use App\Models\User;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class ReportingController extends Controller
{
    public function __construct(
        private readonly ReportingService $reportingService
    ) {}

    /**
     * Display the reporting dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? null : $user->getActiveBranchId();

        return view('admin.reports.index', [
            'eventTypes' => EventReport::EVENT_TYPES,
            'branchId' => $branchId,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Get dashboard statistics API endpoint.
     */
    public function getDashboardStatistics(Request $request): JsonResponse
    {
        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $period = $request->get('period', 'month');

        try {
            // Handle custom date range
            if ($period === 'custom' && $request->has('date_from') && $request->has('date_to')) {
                $dateRange = [
                    'start' => $request->get('date_from'),
                    'end' => $request->get('date_to'),
                ];

                $statistics = $this->reportingService->getDashboardStatisticsForDateRange($dateRange, $branchId);
            } else {
                $statistics = $this->reportingService->getDashboardStatistics($branchId, $period);
            }

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard statistics: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comparative statistics between two periods.
     */
    public function getComparativeStatistics(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $period1 = [
            'start' => $request->get('period1_start'),
            'end' => $request->get('period1_end'),
        ];

        $period2 = [
            'start' => $request->get('period2_start'),
            'end' => $request->get('period2_end'),
        ];

        $comparison = $this->reportingService->getComparativeStatistics($branchId, $period1, $period2);

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Get event reports with filtering.
     */
    public function getEventReports(Request $request): JsonResponse
    {
        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $filters = $request->only([
            'event_type',
            'date_from',
            'date_to',
            'period',
        ]);

        // Get per_page parameter (default to 20, max 100)
        $perPage = min((int) $request->get('per_page', 20), 100);

        try {
            $reports = $this->reportingService->getEventReports($branchId, $filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $reports,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching event reports: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly insights.
     */
    public function getMonthlyInsights(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $insights = $this->reportingService->getMonthlyInsights($branchId, (int) $year, (int) $month);

        return response()->json([
            'success' => true,
            'data' => $insights,
        ]);
    }

    /**
     * Store a new event report.
     */
    public function storeEventReport(EventReportRequest $request): JsonResponse
    {
        Gate::authorize('createReports', [User::class]);

        $data = $request->validated();
        $data['reported_by'] = auth()->id();

        // Transform field names to match database schema
        $data = $this->transformReportData($data);

        try {
            $report = $this->reportingService->createEventReport($data);

            return response()->json([
                'success' => true,
                'message' => 'Event report created successfully.',
                'data' => $report->load(['event', 'reporter']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event report: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an existing event report.
     */
    public function updateEventReport(EventReportRequest $request, EventReport $report): JsonResponse
    {
        // Load the event relationship for authorization
        $report->load('event');
        Gate::authorize('updateReports', [User::class, $report]);

        $data = $request->validated();

        // Transform field names to match database schema
        $data = $this->transformReportData($data);

        try {
            $updatedReport = $this->reportingService->updateEventReport($report, $data);

            return response()->json([
                'success' => true,
                'message' => 'Event report updated successfully.',
                'data' => $updatedReport->load(['event', 'reporter']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event report: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show a specific event report.
     */
    public function showEventReport(EventReport $report): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        return response()->json([
            'success' => true,
            'data' => $report->load(['event', 'reporter']),
        ]);
    }

    /**
     * Delete an event report.
     */
    public function destroyEventReport(EventReport $report): JsonResponse
    {
        // Load the event relationship for authorization
        $report->load('event');
        Gate::authorize('deleteReports', [User::class, $report]);

        try {
            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event report deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event report: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get trend data for charts.
     */
    public function getTrendData(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $period = $request->get('period', 'month');

        try {
            // Handle custom date range
            if ($period === 'custom' && $request->has('date_from') && $request->has('date_to')) {
                $dateRange = [
                    'start' => $request->get('date_from'),
                    'end' => $request->get('date_to'),
                ];

                // Use the private getTrendData method directly for custom ranges
                $trendData = $this->reportingService->getTrendDataForRange($branchId, $dateRange);

                return response()->json([
                    'success' => true,
                    'data' => $trendData,
                ]);
            }

            // Use standard period-based statistics
            $statistics = $this->reportingService->getDashboardStatistics($branchId, $period);

            return response()->json([
                'success' => true,
                'data' => $statistics['trends'] ?? ['attendance_trends' => []],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching trend data: '.$e->getMessage(),
                'data' => ['attendance_trends' => []],
            ], 500);
        }
    }

    /**
     * Export reports to CSV/Excel.
     */
    public function exportReports(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $filters = $request->only([
            'event_type',
            'date_from',
            'date_to',
            'period',
        ]);

        // This would implement actual export functionality
        // For now, returning success message
        return response()->json([
            'success' => true,
            'message' => 'Export functionality will be implemented.',
            'download_url' => '#', // Placeholder
        ]);
    }

    /**
     * Get branch comparison data (Super Admin only).
     */
    public function getBranchComparison(Request $request): JsonResponse
    {
        Gate::authorize('viewAllBranches', [User::class]);

        $period = $request->get('period', 'month');

        // Get statistics for all branches
        $allBranchStats = $this->reportingService->getDashboardStatistics(null, $period);

        return response()->json([
            'success' => true,
            'data' => $allBranchStats,
            'period' => $period,
        ]);
    }

    /**
     * Get available event types.
     */
    public function getEventTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EventReport::EVENT_TYPES,
        ]);
    }

    /**
     * Generate Global Ministry Monthly Report for a specific branch.
     */
    public function getGlobalMinistryMonthlyReport(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        // Cast branch_id to integer if it's not null
        if ($branchId !== null && $branchId !== '') {
            $branchId = (int) $branchId;
        } else {
            $branchId = null;
        }

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        try {
            $report = $this->reportingService->getGlobalMinistryMonthlyReport($branchId, (int) $year, (int) $month);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating Global Ministry Monthly Report: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Global Ministry Monthly Report for all branches (Admin only).
     */
    public function getAllBranchesGlobalMinistryReport(Request $request): JsonResponse
    {
        Gate::authorize('viewAllBranchesReports', [User::class]);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        try {
            $report = $this->reportingService->getAllBranchesGlobalMinistryReport((int) $year, (int) $month);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating organization-wide Global Ministry Monthly Report: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display Super Admin report dashboard.
     */
    public function superAdminDashboard(Request $request): View
    {
        Gate::authorize('viewAllBranchesReports', [User::class]);

        try {
            // Default to this month
            $period = $request->get('period', 'this_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $dateRange = $this->reportingService->calculateSuperAdminDateRange($period, $startDate, $endDate);
            $dashboardData = $this->reportingService->getSuperAdminReportDashboard($dateRange);

            return view('admin.reports.dashboard', [
                'dashboardData' => $dashboardData,
                'currentPeriod' => $period,
                'currentStartDate' => $startDate,
                'currentEndDate' => $endDate,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading Super Admin Dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get Super Admin dashboard data via API (for AJAX calls).
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        Gate::authorize('viewAllBranchesReports', [User::class]);

        $validated = $request->validate([
            'period' => 'required|in:this_week,this_month,last_quarter,this_year,custom',
            'start_date' => 'required_if:period,custom|nullable|date',
            'end_date' => 'required_if:period,custom|nullable|date|after_or_equal:start_date',
        ]);

        try {
            $dateRange = $this->reportingService->calculateSuperAdminDateRange(
                $validated['period'],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            $dashboardData = $this->reportingService->getSuperAdminReportDashboard($dateRange);

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform form field names to match database schema.
     */
    private function transformReportData(array $data): array
    {
        $transformations = [
            // First service
            'male_attendance' => 'attendance_male',
            'female_attendance' => 'attendance_female',
            'children_attendance' => 'attendance_children',
            'online_attendance' => 'attendance_online',
            'cars' => 'number_of_cars',

            // Second service
            'second_male_attendance' => 'second_service_attendance_male',
            'second_female_attendance' => 'second_service_attendance_female',
            'second_children_attendance' => 'second_service_attendance_children',
            'second_online_attendance' => 'second_service_attendance_online',
            'second_cars' => 'second_service_number_of_cars',
            'second_first_time_guests' => 'second_service_first_time_guests',
            'second_converts' => 'second_service_converts',
        ];

        foreach ($transformations as $fromKey => $toKey) {
            if (isset($data[$fromKey])) {
                $data[$toKey] = $data[$fromKey];
                unset($data[$fromKey]);
            }
        }

        // Ensure required fields have default values to prevent NULL constraint violations
        if (! isset($data['number_of_cars'])) {
            $data['number_of_cars'] = 0;
        }
        if (! isset($data['second_service_number_of_cars'])) {
            $data['second_service_number_of_cars'] = 0;
        }

        // Transform has_second_service to is_multi_service
        if (isset($data['has_second_service'])) {
            $data['is_multi_service'] = (bool) $data['has_second_service'];
            unset($data['has_second_service']);
        }

        // Ensure report_date is set from event_date
        if (isset($data['event_date'])) {
            $data['report_date'] = $data['event_date'];
        }

        return $data;
    }
}
