<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Business;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class BusinessHoursService
{
    private const DAY_KEYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * @return array{
     *     is_open_now: bool,
     *     status_label: string,
     *     hours_summary: string|null,
     *     closed_all_day: bool
     * }|null
     */
    public function statusForBusiness(Business $business, ?CarbonInterface $at = null): ?array
    {
        $hours = $business->working_hours;

        if (! is_array($hours) || $hours === []) {
            return null;
        }

        $at = Carbon::parse($at ?? now());
        $dayKey = strtolower($at->englishDayOfWeek);

        if (! in_array($dayKey, self::DAY_KEYS, true)) {
            return null;
        }

        $today = $hours[$dayKey] ?? null;

        if (! is_array($today)) {
            return null;
        }

        if ($this->isClosedAllDay($today)) {
            return [
                'is_open_now' => false,
                'status_label' => 'Closed',
                'hours_summary' => null,
                'closed_all_day' => true,
            ];
        }

        $open = $this->normalizeTimeString($today['open'] ?? null);
        $close = $this->normalizeTimeString($today['close'] ?? null);

        if ($open === null || $close === null) {
            return null;
        }

        $isOpenNow = $this->isOpenAt($open, $close, $at);

        return [
            'is_open_now' => $isOpenNow,
            'status_label' => $isOpenNow ? 'Open now' : 'Closed',
            'hours_summary' => $this->formatHoursSummary($open, $close),
            'closed_all_day' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $dayHours
     */
    private function isClosedAllDay(array $dayHours): bool
    {
        return filter_var($dayHours['closed'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function isOpenAt(string $open, string $close, CarbonInterface $at): bool
    {
        $moment = Carbon::parse($at);
        $openAt = $moment->copy()->setTimeFromTimeString($open);
        $closeAt = $moment->copy()->setTimeFromTimeString($close);

        if ($closeAt->greaterThan($openAt)) {
            return $moment->greaterThanOrEqualTo($openAt) && $moment->lessThan($closeAt);
        }

        // Overnight hours (e.g. 22:00 – 02:00).
        return $moment->greaterThanOrEqualTo($openAt) || $moment->lessThan($closeAt);
    }

    private function normalizeTimeString(mixed $time): ?string
    {
        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        $time = trim($time);

        if (preg_match('/^\d{1,2}:\d{2}$/', $time) === 1) {
            return $time.':00';
        }

        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time) === 1) {
            return $time;
        }

        return null;
    }

    private function formatHoursSummary(string $open, string $close): string
    {
        return substr($open, 0, 5).' – '.substr($close, 0, 5);
    }
}
