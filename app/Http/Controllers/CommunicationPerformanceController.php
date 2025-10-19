<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CommunicationPerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CommunicationPerformanceController extends Controller
{
    public function __construct(
        private readonly CommunicationPerformanceService $performanceService
    ) {}

    /**
     * Get communication performance metrics.
     */
    public function metrics(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $days = max(1, min(365, (int) $days)); // Limit between 1 and 365 days

        $metrics = $this->performanceService->getProviderMetrics($days);

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'period_days' => $days,
        ]);
    }

    /**
     * Get daily communication volume.
     */
    public function dailyVolume(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $days = max(1, min(365, (int) $days));

        $volume = $this->performanceService->getDailyVolume($days);

        return response()->json([
            'success' => true,
            'data' => $volume,
            'period_days' => $days,
        ]);
    }

    /**
     * Get queue performance metrics.
     */
    public function queueMetrics(): JsonResponse
    {
        $metrics = $this->performanceService->getQueueMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get recent communication failures.
     */
    public function recentFailures(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 50);
        $limit = max(1, min(100, (int) $limit));

        $failures = $this->performanceService->getRecentFailures($limit);

        return response()->json([
            'success' => true,
            'data' => $failures,
            'limit' => $limit,
        ]);
    }

    /**
     * Get comprehensive performance summary.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->performanceService->getPerformanceSummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}

