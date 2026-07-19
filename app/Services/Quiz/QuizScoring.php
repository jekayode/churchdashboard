<?php

declare(strict_types=1);

namespace App\Services\Quiz;

/**
 * Kahoot's scoring, which is worth copying because the room already expects it:
 * a correct answer is worth between half and all of the points depending on how
 * quickly it came, and a wrong answer is worth nothing.
 *
 * Answering instantly is not rewarded over answering carefully within a second
 * or two — the curve is gentle enough that guessing early is a bad strategy.
 */
final class QuizScoring
{
    /** The slowest correct answer still earns this share of the points. */
    private const FLOOR = 0.5;

    public static function award(int $basePoints, int $responseMs, int $timeLimitMs): int
    {
        if ($timeLimitMs <= 0) {
            return $basePoints;
        }

        $ratio = min(1.0, max(0.0, $responseMs / $timeLimitMs));

        return (int) round($basePoints * (1 - $ratio * (1 - self::FLOOR)));
    }
}
