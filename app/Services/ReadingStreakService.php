<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Models\MemberReadingProgress;
use Carbon\CarbonImmutable;

/**
 * Reading streaks.
 *
 * Streaks are counted from `completed_on`, the member's own local date, so a
 * reader in Lagos and one in London both get a fair day boundary. The streak
 * spans plans: switching from Bible in a Year to a devotional series does not
 * reset it.
 */
final class ReadingStreakService
{
    /**
     * @return array{current: int, longest: int, completed_today: bool, last_completed_on: ?string, total_days: int}
     */
    public function summary(Member $member, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::now()->startOfDay();

        $dates = $this->completedDates($member);

        return [
            'current' => $this->currentStreak($dates, $today),
            'longest' => $this->longestStreak($dates),
            'completed_today' => in_array($today->toDateString(), $dates, true),
            'last_completed_on' => $dates[0] ?? null,
            'total_days' => count($dates),
        ];
    }

    /**
     * Distinct completion dates, newest first.
     *
     * @return list<string>
     */
    private function completedDates(Member $member): array
    {
        return MemberReadingProgress::query()
            ->where('member_id', $member->id)
            ->orderByDesc('completed_on')
            ->pluck('completed_on')
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Consecutive days up to today.
     *
     * Yesterday still counts, so a member who has not read *yet* today keeps
     * their streak until the day is actually over.
     *
     * @param  list<string>  $dates  newest first
     */
    private function currentStreak(array $dates, CarbonImmutable $today): int
    {
        if ($dates === []) {
            return 0;
        }

        $latest = CarbonImmutable::parse($dates[0])->startOfDay();
        $daysSince = $latest->diffInDays($today);

        // Older than yesterday means the streak is broken.
        if ($daysSince > 1) {
            return 0;
        }

        $streak = 1;
        $expected = $latest->subDay();

        foreach (array_slice($dates, 1) as $date) {
            if (CarbonImmutable::parse($date)->startOfDay()->toDateString() !== $expected->toDateString()) {
                break;
            }

            $streak++;
            $expected = $expected->subDay();
        }

        return $streak;
    }

    /**
     * @param  list<string>  $dates  newest first
     */
    private function longestStreak(array $dates): int
    {
        if ($dates === []) {
            return 0;
        }

        $longest = 1;
        $run = 1;

        for ($i = 1, $count = count($dates); $i < $count; $i++) {
            $previous = CarbonImmutable::parse($dates[$i - 1])->startOfDay();
            $current = CarbonImmutable::parse($dates[$i])->startOfDay();

            if ($current->addDay()->toDateString() === $previous->toDateString()) {
                $run++;
                $longest = max($longest, $run);

                continue;
            }

            $run = 1;
        }

        return $longest;
    }
}
