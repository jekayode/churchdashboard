<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Services\Quiz\QuizScoring;
use PHPUnit\Framework\TestCase;

final class QuizScoringTest extends TestCase
{
    public function test_an_instant_answer_earns_the_full_points(): void
    {
        $this->assertSame(1000, QuizScoring::award(1000, 0, 10000));
    }

    public function test_the_slowest_correct_answer_still_earns_half(): void
    {
        $this->assertSame(500, QuizScoring::award(1000, 10000, 10000));
    }

    public function test_points_fall_off_evenly_across_the_window(): void
    {
        $this->assertSame(750, QuizScoring::award(1000, 5000, 10000));
        $this->assertSame(875, QuizScoring::award(1000, 2500, 10000));
    }

    public function test_faster_always_beats_slower(): void
    {
        $previous = PHP_INT_MAX;

        foreach (range(0, 10000, 500) as $responseMs) {
            $points = QuizScoring::award(1000, $responseMs, 10000);
            $this->assertLessThan($previous, $points, "Points must keep falling at {$responseMs}ms");
            $previous = $points;
        }
    }

    public function test_an_answer_beyond_the_limit_is_clamped_rather_than_going_negative(): void
    {
        $this->assertSame(500, QuizScoring::award(1000, 999999, 10000));
    }

    public function test_a_zero_time_limit_does_not_divide_by_zero(): void
    {
        $this->assertSame(1000, QuizScoring::award(1000, 5000, 0));
    }
}
