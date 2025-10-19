<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommunicationLog;
use Illuminate\Support\Facades\DB;

final class CommunicationPerformanceService
{
    /**
     * Get performance metrics for communication providers.
     */
    public function getProviderMetrics(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $metrics = CommunicationLog::select([
            'type',
            DB::raw('CASE 
                    WHEN type = "email" THEN JSON_EXTRACT(communication_settings.email_config, "$.provider")
                    WHEN type = "sms" THEN JSON_EXTRACT(communication_settings.sms_config, "$.provider")
                    ELSE "unknown"
                END as provider'),
            DB::raw('COUNT(*) as total_attempts'),
            DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful_sends'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_sends'),
            DB::raw('AVG(CASE WHEN status = "sent" THEN 
                    TIMESTAMPDIFF(MICROSECOND, created_at, sent_at) / 1000 
                END) as avg_response_time_ms'),
        ])
            ->join('communication_settings', 'communication_logs.branch_id', '=', 'communication_settings.branch_id')
            ->where('communication_logs.created_at', '>=', $startDate)
            ->groupBy('type', 'provider')
            ->get()
            ->map(function ($metric) {
                $successRate = $metric->total_attempts > 0
                    ? round(($metric->successful_sends / $metric->total_attempts) * 100, 2)
                    : 0;

                return [
                    'type' => $metric->type,
                    'provider' => $metric->provider ?? 'unknown',
                    'total_attempts' => (int) $metric->total_attempts,
                    'successful_sends' => (int) $metric->successful_sends,
                    'failed_sends' => (int) $metric->failed_sends,
                    'success_rate' => $successRate,
                    'avg_response_time_ms' => $metric->avg_response_time_ms
                        ? round($metric->avg_response_time_ms, 2)
                        : null,
                ];
            });

        return $metrics->toArray();
    }

    /**
     * Get daily communication volume.
     */
    public function getDailyVolume(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return CommunicationLog::select([
            DB::raw('DATE(created_at) as date'),
            'type',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed'),
        ])
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'type')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get queue performance metrics.
     */
    public function getQueueMetrics(): array
    {
        $totalJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->whereNull('reserved_at')->count();
        $processingJobs = DB::table('jobs')->whereNotNull('reserved_at')->count();

        return [
            'total_jobs' => $totalJobs,
            'pending_jobs' => $pendingJobs,
            'processing_jobs' => $processingJobs,
            'failed_jobs' => $failedJobs,
            'success_rate' => $totalJobs > 0
                ? round((($totalJobs - $failedJobs) / $totalJobs) * 100, 2)
                : 100,
        ];
    }

    /**
     * Get recent failures with details.
     */
    public function getRecentFailures(int $limit = 50): array
    {
        return CommunicationLog::where('status', 'failed')
            ->with(['branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->type,
                    'recipient' => $log->recipient,
                    'branch_name' => $log->branch->name ?? 'Unknown',
                    'error_message' => $log->error_message,
                    'created_at' => $log->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get performance summary.
     */
    public function getPerformanceSummary(): array
    {
        $providerMetrics = $this->getProviderMetrics(7); // Last 7 days
        $queueMetrics = $this->getQueueMetrics();
        $recentFailures = $this->getRecentFailures(10);

        return [
            'provider_metrics' => $providerMetrics,
            'queue_metrics' => $queueMetrics,
            'recent_failures' => $recentFailures,
            'generated_at' => now()->toISOString(),
        ];
    }
}

