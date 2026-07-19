<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizPhase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The timeline is what every surface derives its view from, so it is tested at
 * the boundaries rather than the middles — the moment a question opens, the
 * moment it closes, and the moment the last reveal ends.
 */
final class QuizTimelineTest extends TestCase
{
    use RefreshDatabase;

    /** Three 10s questions with 5s reveals: each question occupies 15s. */
    private function quizWithQuestions(int $count = 3, int $limit = 10, int $reveal = 5): Quiz
    {
        $quiz = Quiz::factory()->running(now())->create([
            'seconds_per_question' => $limit,
            'reveal_seconds' => $reveal,
        ]);

        for ($i = 1; $i <= $count; $i++) {
            QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => $i]);
        }

        return $quiz->fresh(['questions']);
    }

    public function test_a_quiz_that_has_not_started_is_in_the_lobby(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);

        $state = $quiz->fresh(['questions'])->timeline()->stateAt(now());

        $this->assertSame(QuizPhase::Lobby, $state->phase);
        $this->assertNull($state->question);
    }

    public function test_a_started_quiz_opens_on_its_first_question(): void
    {
        $quiz = $this->quizWithQuestions();

        $state = $quiz->timeline()->stateAt($quiz->started_at->copy()->addSecond());

        $this->assertSame(QuizPhase::Question, $state->phase);
        $this->assertSame(0, $state->questionIndex);
        $this->assertSame(1, $state->questionNumber());
        $this->assertSame(9000, $state->remainingMs);
        $this->assertTrue($state->isAnswerable());
    }

    public function test_answers_close_the_instant_the_limit_is_reached(): void
    {
        $quiz = $this->quizWithQuestions();
        $start = $quiz->started_at;

        $justInside = $quiz->timeline()->stateAt($start->copy()->addMilliseconds(9999));
        $this->assertSame(QuizPhase::Question, $justInside->phase);
        $this->assertTrue($justInside->isAnswerable());

        $atTheLimit = $quiz->timeline()->stateAt($start->copy()->addMilliseconds(10000));
        $this->assertSame(QuizPhase::Reveal, $atTheLimit->phase);
        $this->assertFalse($atTheLimit->isAnswerable(), 'An answer landing exactly on the limit is late');
    }

    public function test_the_next_question_follows_the_reveal(): void
    {
        $quiz = $this->quizWithQuestions();
        $start = $quiz->started_at;

        // 10s question + 5s reveal = question two opens at 15s.
        $this->assertSame(0, $quiz->timeline()->stateAt($start->copy()->addSeconds(14))->questionIndex);

        $second = $quiz->timeline()->stateAt($start->copy()->addSeconds(15));
        $this->assertSame(QuizPhase::Question, $second->phase);
        $this->assertSame(1, $second->questionIndex);
        $this->assertSame(15000, $second->questionStartOffsetMs);
    }

    public function test_the_quiz_finishes_after_the_last_reveal(): void
    {
        $quiz = $this->quizWithQuestions(3);
        $start = $quiz->started_at;

        // Three questions of 15s each: the run ends at 45s.
        $this->assertSame(QuizPhase::Reveal, $quiz->timeline()->stateAt($start->copy()->addSeconds(44))->phase);
        $this->assertSame(QuizPhase::Finished, $quiz->timeline()->stateAt($start->copy()->addSeconds(45))->phase);
    }

    public function test_a_question_may_override_the_quiz_time_limit(): void
    {
        $quiz = Quiz::factory()->running(now())->create(['seconds_per_question' => 10, 'reveal_seconds' => 5]);
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1, 'time_limit_seconds' => 30]);
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 2]);
        $quiz = $quiz->fresh(['questions']);

        // First question runs 30s, not 10s, so question two opens at 35s.
        $this->assertSame(0, $quiz->timeline()->stateAt($quiz->started_at->copy()->addSeconds(29))->questionIndex);
        $this->assertSame(1, $quiz->timeline()->stateAt($quiz->started_at->copy()->addSeconds(35))->questionIndex);
    }

    public function test_pausing_freezes_the_run_where_it_stands(): void
    {
        $quiz = $this->quizWithQuestions();
        $start = $quiz->started_at;

        $quiz->update(['paused_at' => $start->copy()->addSeconds(4)]);
        $quiz = $quiz->fresh(['questions']);

        // A minute of wall clock passes, but the quiz has not moved.
        $state = $quiz->timeline()->stateAt($start->copy()->addSeconds(64));

        $this->assertSame(QuizPhase::Question, $state->phase);
        $this->assertSame(0, $state->questionIndex);
        $this->assertSame(6000, $state->remainingMs, 'Still 6s left on question one');
        $this->assertTrue($state->paused);
        $this->assertFalse($state->isAnswerable(), 'A paused quiz must not accept answers');
    }

    public function test_resuming_carries_the_paused_time_forward(): void
    {
        $quiz = $this->quizWithQuestions();
        $start = $quiz->started_at;

        // Paused at 4s for a minute, then resumed.
        $quiz->update(['paused_at' => null, 'paused_ms' => 60000]);
        $quiz = $quiz->fresh(['questions']);

        $state = $quiz->timeline()->stateAt($start->copy()->addSeconds(65));

        $this->assertSame(0, $state->questionIndex, 'Only 5s of quiz time has actually passed');
        $this->assertSame(5000, $state->remainingMs);
    }

    public function test_a_quiz_with_no_questions_stays_in_the_lobby(): void
    {
        $quiz = Quiz::factory()->running(now())->create();

        $this->assertSame(QuizPhase::Lobby, $quiz->fresh(['questions'])->timeline()->stateAt(now())->phase);
    }

    public function test_a_finished_quiz_reports_finished_whatever_the_clock_says(): void
    {
        $quiz = $this->quizWithQuestions();
        $quiz->update(['status' => 'finished', 'finished_at' => now()]);

        $state = $quiz->fresh(['questions'])->timeline()->stateAt($quiz->started_at->copy()->addSeconds(2));

        $this->assertSame(QuizPhase::Finished, $state->phase, 'Ending early must override the arithmetic');
    }
}
