<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CommunicationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class CommunicationLogController extends Controller
{
    /**
     * Display a listing of communication logs.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');
        $perPage = min($request->integer('per_page', 15), 100);

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $query = CommunicationLog::where('branch_id', $branch->id)
            ->with(['user:id,name,email', 'template:id,name,type', 'branch:id,name']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Search in recipient or subject
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->integer('template_id'));
        }

        // Filter by user (sender)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
            'filters' => [
                'types' => ['email', 'sms'],
                'statuses' => ['pending', 'sent', 'failed', 'delivered', 'bounced'],
            ],
        ]);
    }

    /**
     * Display the specified communication log.
     */
    public function show(CommunicationLog $log): JsonResponse
    {
        Gate::authorize('view', $log->branch);

        $log->load(['user:id,name,email', 'template:id,name,type,subject', 'branch:id,name']);

        return response()->json(['log' => $log]);
    }

    /**
     * Get communication statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $query = CommunicationLog::where('branch_id', $branch->id);

        // Apply date filter if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Get overall statistics
        $totalLogs = (clone $query)->count();
        $emailLogs = (clone $query)->email()->count();
        $smsLogs = (clone $query)->sms()->count();
        $sentLogs = (clone $query)->sent()->count();
        $failedLogs = (clone $query)->failed()->count();
        $pendingLogs = (clone $query)->pending()->count();

        // Calculate rates
        $successRate = $totalLogs > 0 ? round(($sentLogs / $totalLogs) * 100, 2) : 0;
        $failureRate = $totalLogs > 0 ? round(($failedLogs / $totalLogs) * 100, 2) : 0;

        // Get daily statistics for the last 30 days
        $dailyStats = CommunicationLog::where('branch_id', $branch->id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, 
                        SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                        SUM(CASE WHEN type = "email" THEN 1 ELSE 0 END) as email,
                        SUM(CASE WHEN type = "sms" THEN 1 ELSE 0 END) as sms')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top templates by usage
        $topTemplates = CommunicationLog::where('branch_id', $branch->id)
            ->whereNotNull('template_id')
            ->with('template:id,name,type')
            ->selectRaw('template_id, COUNT(*) as usage_count')
            ->groupBy('template_id')
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'statistics' => [
                'total_messages' => $totalLogs,
                'email_messages' => $emailLogs,
                'sms_messages' => $smsLogs,
                'sent_messages' => $sentLogs,
                'failed_messages' => $failedLogs,
                'pending_messages' => $pendingLogs,
                'success_rate' => $successRate,
                'failure_rate' => $failureRate,
            ],
            'daily_stats' => $dailyStats,
            'top_templates' => $topTemplates,
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
        ]);
    }

    /**
     * Get communication trends.
     */
    public function trends(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $query = CommunicationLog::where('branch_id', $branch->id);

        // Set date range and grouping based on period
        switch ($period) {
            case 'daily':
                $query->whereDate('created_at', '>=', now()->subDays(30));
                $groupBy = 'DATE(created_at)';
                $dateFormat = '%Y-%m-%d';
                break;
            case 'weekly':
                $query->whereDate('created_at', '>=', now()->subWeeks(12));
                $groupBy = 'YEARWEEK(created_at)';
                $dateFormat = '%Y-W%u';
                break;
            case 'monthly':
            default:
                $query->whereDate('created_at', '>=', now()->subMonths(12));
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                $dateFormat = '%Y-%m';
                break;
        }

        $trends = $query->selectRaw("
                {$groupBy} as period,
                DATE_FORMAT(created_at, '{$dateFormat}') as formatted_period,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as email,
                SUM(CASE WHEN type = 'sms' THEN 1 ELSE 0 END) as sms
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'trends' => $trends,
            'period' => $period,
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
        ]);
    }

    /**
     * Export communication logs.
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $query = CommunicationLog::where('branch_id', $branch->id)
            ->with(['user:id,name,email', 'template:id,name,type']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->limit(10000)->get();

        // Format for export
        $exportData = $logs->map(function ($log) {
            return [
                'Date' => $log->created_at->format('Y-m-d H:i:s'),
                'Type' => ucfirst($log->type),
                'Recipient' => $log->recipient,
                'Subject' => $log->subject ?? 'N/A',
                'Template' => $log->template?->name ?? 'N/A',
                'Status' => ucfirst($log->status),
                'Sender' => $log->user?->name ?? 'System',
                'Sent At' => $log->sent_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Error' => $log->error_message ?? 'N/A',
            ];
        });

        return response()->json([
            'data' => $exportData,
            'total' => $logs->count(),
            'filename' => "communication_logs_{$branch->name}_{now()->format('Y-m-d')}.csv",
        ]);
    }
}
