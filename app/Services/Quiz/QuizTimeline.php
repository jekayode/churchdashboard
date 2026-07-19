<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Works out where a running quiz is, purely from when it started.
 *
 * The pastor's only live job is to start the quiz, so questions advance on their
 * own. That makes the whole run derivable arithmetic rather than a sequence of
 * events someone had to witness: question three begins at started_at plus the
 * durations of the two before it, full stop.
 *
 * The practical payoff is recovery. A phone that locked, dropped signal, or
 * joined during question three asks "what's happening?" and gets a complete
 * answer, instead of being stranded because it missed a broadcast.
 *
 * Pausing is the one thing that would break that arithmetic, so paused time is
 * banked into paused_ms and subtracted from elapsed. started_at never moves.
 */
final class QuizTimeline
{
    /** @var Collection<int, QuizQuestion> */
    private Collection $questions;

    /**
     * @param  Collection<int, QuizQuestion>  $questions
     */
    public function __construct(private readonly Quiz $quiz, Collection $questions)
    {
        $this->questions = $questions->sortBy('position')->values();
    }

    public function stateAt(CarbonInterface $at): QuizState
    {
        $count = $this->questions->count();

        if ($this->quiz->status === 'finished' || $this->quiz->finished_at !== null) {
            return new QuizState(phase: QuizPhase::Finished, questionCount: $count);
        }

        if ($this->quiz->status !== 'running' || $this->quiz->started_at === null || $count === 0) {
            return new QuizState(phase: QuizPhase::Lobby, questionCount: $count);
        }

        $elapsed = $this->elapsedMs($at);
        $paused = $this->quiz->paused_at !== null;
        $offset = 0;

        foreach ($this->questions as $index => $question) {
            $limitMs = $question->effectiveTimeLimit() * 1000;
            $revealMs = $this->quiz->reveal_seconds * 1000;
            $into = $elapsed - $offset;

            if ($into < $limitMs) {
                return new QuizState(
                    phase: QuizPhase::Question,
                    questionIndex: $index,
                    question: $question,
                    remainingMs: $limitMs - $into,
                    questionStartOffsetMs: $offset,
                    questionCount: $count,
                    paused: $paused,
                );
            }

            if ($into < $limitMs + $revealMs) {
                return new QuizState(
                    phase: QuizPhase::Reveal,
                    questionIndex: $index,
                    question: $question,
                    remainingMs: $limitMs + $revealMs - $into,
                    questionStartOffsetMs: $offset,
                    questionCount: $count,
                    paused: $paused,
                );
            }

            $offset += $limitMs + $revealMs;
        }

        return new QuizState(phase: QuizPhase::Finished, questionCount: $count);
    }

    /**
     * Time the quiz has actually been running, with any paused stretches taken
     * out — including the one still open if it is paused right now.
     */
    public function elapsedMs(CarbonInterface $at): int
    {
        if ($this->quiz->started_at === null) {
            return 0;
        }

        $elapsed = $this->quiz->started_at->diffInMilliseconds($at, absolute: false);
        $elapsed -= $this->quiz->paused_ms;

        if ($this->quiz->paused_at !== null) {
            $elapsed -= $this->quiz->paused_at->diffInMilliseconds($at, absolute: false);
        }

        return (int) max(0, $elapsed);
    }

    /** Total run time, used to know when the last reveal has finished. */
    public function totalDurationMs(): int
    {
        return $this->questions->sum(
            fn (QuizQuestion $q): int => ($q->effectiveTimeLimit() + $this->quiz->reveal_seconds) * 1000
        );
    }
}
