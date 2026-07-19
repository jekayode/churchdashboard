<?php

declare(strict_types=1);

namespace App\Services\Quiz;

use App\Models\QuizQuestion;

/**
 * Where a quiz is at one instant. Every surface — projector, phone, host —
 * renders from this, which is what keeps them agreeing with each other without
 * needing to have heard the same events.
 */
final readonly class QuizState
{
    public function __construct(
        public QuizPhase $phase,
        /** Zero-based; null in lobby and when finished. */
        public ?int $questionIndex = null,
        public ?QuizQuestion $question = null,
        /** Time left in the current phase. Drives the countdown ring. */
        public int $remainingMs = 0,
        /** Milliseconds from the quiz start to when this question opened. The
         *  anchor a response time is measured against. */
        public int $questionStartOffsetMs = 0,
        public int $questionCount = 0,
        public bool $paused = false,
    ) {}

    public function isAnswerable(): bool
    {
        return $this->phase === QuizPhase::Question && ! $this->paused;
    }

    public function questionNumber(): ?int
    {
        return $this->questionIndex === null ? null : $this->questionIndex + 1;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'phase' => $this->phase->value,
            'paused' => $this->paused,
            'question_index' => $this->questionIndex,
            'question_number' => $this->questionNumber(),
            'question_count' => $this->questionCount,
            'remaining_ms' => $this->remainingMs,
        ];
    }
}
