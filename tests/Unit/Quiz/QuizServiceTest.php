<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizParticipant;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizException;
use App\Services\Quiz\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class QuizServiceTest extends TestCase
{
    use RefreshDatabase;

    private QuizService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuizService;
    }

    /** A quiz of two 10s questions with 5s reveals, already running. */
    private function runningQuiz(): Quiz
    {
        $quiz = Quiz::factory()->running(now())->create([
            'seconds_per_question' => 10,
            'reveal_seconds' => 5,
        ]);

        foreach ([1, 2] as $position) {
            $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => $position]);
            QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Right']);
            QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Wrong']);
        }

        return $quiz->fresh(['questions.options']);
    }

    private function optionFor(Quiz $quiz, int $questionIndex, bool $correct): QuizOption
    {
        return $quiz->questions[$questionIndex]->options->firstWhere('is_correct', $correct);
    }

    // Joining ------------------------------------------------------------

    public function test_a_guest_joins_with_just_a_name(): void
    {
        $quiz = Quiz::factory()->lobby()->create();

        $participant = $this->service->join($quiz, null, null, 'Tobi');

        $this->assertSame('Tobi', $participant->display_name);
        $this->assertTrue($participant->isGuest());
        $this->assertNotEmpty($participant->guest_token, 'A guest needs a token, or their score can never be claimed');
    }

    public function test_a_new_participant_starts_on_real_zeroes(): void
    {
        $quiz = Quiz::factory()->lobby()->create();

        $participant = $this->service->join($quiz, null, null, 'Tobi');

        // These are database defaults, which a freshly created model does not
        // read back — leaving nulls that broke the first leaderboard comparison.
        $this->assertSame(0, $participant->score);
        $this->assertSame(0, $participant->total_response_ms);
        $this->assertSame(0, $participant->correct_count);
        $this->assertSame(1, $this->service->placementFor($participant));
    }

    public function test_a_guest_rejoining_lands_back_on_their_own_score(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $first = $this->service->join($quiz, null, null, 'Tobi');

        $again = $this->service->join($quiz, null, $first->guest_token, 'Tobi');

        $this->assertSame($first->id, $again->id, 'A locked phone must not restart on zero');
        $this->assertSame(1, $quiz->participants()->count());
    }

    public function test_a_member_joins_under_their_own_name(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $member = Member::factory()->create(['first_name' => 'Emmanuel', 'surname' => 'Joseph']);

        $participant = $this->service->join($quiz, $member, null, null);

        $this->assertSame('Emmanuel Joseph', $participant->display_name);
        $this->assertFalse($participant->isGuest());
    }

    public function test_a_member_with_no_name_on_file_still_gets_a_label(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $member = Member::factory()->create(['first_name' => '', 'surname' => '']);

        $participant = $this->service->join($quiz, $member, null, null);

        $this->assertSame('Member', $participant->display_name);
    }

    public function test_a_member_joining_twice_is_the_same_player(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $member = Member::factory()->create();

        $first = $this->service->join($quiz, $member, null, null);
        $again = $this->service->join($quiz, $member, null, null);

        $this->assertSame($first->id, $again->id);
    }

    public function test_an_unacceptable_guest_name_is_refused(): void
    {
        $quiz = Quiz::factory()->lobby()->create();

        $this->expectException(QuizException::class);
        $this->service->join($quiz, null, null, 'sh1t');
    }

    public function test_guests_can_be_shut_out_of_a_members_only_quiz(): void
    {
        $quiz = Quiz::factory()->lobby()->create(['allow_guests' => false]);

        $this->expectExceptionMessage('Sign in to join this quiz.');
        $this->service->join($quiz, null, null, 'Tobi');
    }

    public function test_a_draft_quiz_cannot_be_joined(): void
    {
        $quiz = Quiz::factory()->create(['status' => 'draft']);

        $this->expectException(QuizException::class);
        $this->service->join($quiz, null, null, 'Tobi');
    }

    public function test_someone_removed_by_the_host_cannot_rejoin(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $participant = $this->service->join($quiz, null, null, 'Tobi');
        $this->service->removeParticipant($participant);

        $this->expectExceptionMessage('You have been removed from this quiz.');
        $this->service->join($quiz, null, $participant->guest_token, 'Tobi');
    }

    // Answering ----------------------------------------------------------

    public function test_a_correct_answer_scores_on_a_speed_curve(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');

        $answer = $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, true)->id,
            $quiz->started_at->copy()->addSeconds(5),
        );

        $this->assertTrue($answer->is_correct);
        $this->assertSame(5000, $answer->response_ms);
        $this->assertSame(750, $answer->points_awarded, 'Half way through the window is three quarters of the points');
        $this->assertSame(750, $participant->fresh()->score);
    }

    public function test_a_wrong_answer_scores_nothing_but_is_still_recorded(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');

        $answer = $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, false)->id,
            $quiz->started_at->copy()->addSeconds(2),
        );

        $this->assertFalse($answer->is_correct);
        $this->assertSame(0, $answer->points_awarded);
        $this->assertSame(0, $participant->fresh()->score);
        $this->assertSame(2000, $answer->response_ms, 'Wrong answers still count toward the tie-break');
    }

    public function test_the_response_time_is_the_servers_not_the_phones(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');

        // Nothing in the call signature lets a client claim it answered instantly.
        $answer = $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, true)->id,
            $quiz->started_at->copy()->addSeconds(9),
        );

        $this->assertSame(9000, $answer->response_ms);
    }

    public function test_answering_twice_is_refused(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');
        $at = $quiz->started_at->copy()->addSeconds(2);

        $this->service->submitAnswer($participant, $this->optionFor($quiz, 0, true)->id, $at);

        $this->expectExceptionMessage('You have already answered this question.');
        $this->service->submitAnswer($participant, $this->optionFor($quiz, 0, false)->id, $at);
    }

    public function test_a_second_answer_cannot_change_the_score(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');
        $at = $quiz->started_at->copy()->addSeconds(2);

        $this->service->submitAnswer($participant, $this->optionFor($quiz, 0, false)->id, $at);
        $scoreAfterWrongAnswer = $participant->fresh()->score;

        try {
            $this->service->submitAnswer($participant, $this->optionFor($quiz, 0, true)->id, $at);
        } catch (QuizException) {
            // expected
        }

        $this->assertSame($scoreAfterWrongAnswer, $participant->fresh()->score);
        $this->assertSame(1, $participant->answers()->count());
    }

    public function test_an_answer_after_the_bell_is_refused(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');

        $this->expectExceptionMessage('That question has closed.');
        $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, true)->id,
            $quiz->started_at->copy()->addSeconds(11),
        );
    }

    public function test_an_answer_to_a_question_the_room_has_left_is_refused(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');

        // Question two is live at 16s; an answer to question one arrives late.
        $this->expectExceptionMessage('The quiz has moved on to another question.');
        $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, true)->id,
            $quiz->started_at->copy()->addSeconds(16),
        );
    }

    public function test_a_paused_quiz_accepts_nothing(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');
        $this->service->pause($quiz);

        $this->expectException(QuizException::class);
        $this->service->submitAnswer($participant, $this->optionFor($quiz, 0, true)->id, now());
    }

    public function test_someone_removed_mid_game_cannot_answer(): void
    {
        $quiz = $this->runningQuiz();
        $participant = $this->service->join($quiz, null, null, 'Tobi');
        $this->service->removeParticipant($participant);

        $this->expectException(QuizException::class);
        $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 0, true)->id,
            $quiz->started_at->copy()->addSeconds(2),
        );
    }

    public function test_a_late_joiner_plays_from_where_the_room_is(): void
    {
        $quiz = $this->runningQuiz();
        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(16));

        $participant = $this->service->join($quiz, null, null, 'Latecomer');
        $answer = $this->service->submitAnswer(
            $participant,
            $this->optionFor($quiz, 1, true)->id,
            $quiz->started_at->copy()->addSeconds(16),
        );

        $this->assertTrue($answer->is_correct);
        $this->assertSame(1000, $answer->response_ms, 'Timed from when question two opened, not from the quiz start');
        $this->assertSame(1, $participant->fresh()->answers()->count(), 'They simply have nothing for question one');

        Carbon::setTestNow();
    }

    // Standings ----------------------------------------------------------

    public function test_the_leaderboard_is_ordered_by_score(): void
    {
        $quiz = $this->runningQuiz();
        QuizParticipant::factory()->create(['quiz_id' => $quiz->id, 'display_name' => 'Low', 'score' => 100]);
        QuizParticipant::factory()->create(['quiz_id' => $quiz->id, 'display_name' => 'High', 'score' => 900]);

        $board = $this->service->leaderboard($quiz);

        $this->assertSame('High', $board[0]['name']);
        $this->assertSame(1, $board[0]['rank']);
    }

    public function test_a_tie_is_broken_by_who_answered_faster_overall(): void
    {
        $quiz = $this->runningQuiz();
        $slow = QuizParticipant::factory()->create([
            'quiz_id' => $quiz->id, 'display_name' => 'Slow', 'score' => 500, 'total_response_ms' => 9000,
        ]);
        $fast = QuizParticipant::factory()->create([
            'quiz_id' => $quiz->id, 'display_name' => 'Fast', 'score' => 500, 'total_response_ms' => 3000,
        ]);

        $board = $this->service->leaderboard($quiz);

        $this->assertSame('Fast', $board[0]['name'], 'Equal points must still produce one winner');
        $this->assertSame('Slow', $board[1]['name']);
        $this->assertSame(1, $this->service->placementFor($fast));
        $this->assertSame(2, $this->service->placementFor($slow));
    }

    public function test_a_removed_player_drops_off_the_leaderboard(): void
    {
        $quiz = $this->runningQuiz();
        $participant = QuizParticipant::factory()->create(['quiz_id' => $quiz->id, 'display_name' => 'Gone', 'score' => 999]);
        $this->service->removeParticipant($participant);

        $this->assertSame([], $this->service->leaderboard($quiz));
    }

    // Guest to member ----------------------------------------------------

    public function test_a_guest_score_is_claimed_on_signing_up(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $guest = $this->service->join($quiz, null, null, 'Tobi');
        $member = Member::factory()->create();

        $claimed = $this->service->claimGuestScores($member, $guest->guest_token);

        $this->assertSame(1, $claimed);
        $this->assertSame($member->id, $guest->fresh()->member_id);
    }

    public function test_claiming_covers_every_quiz_played_on_that_device(): void
    {
        $member = Member::factory()->create();
        $token = 'device-token-abc';

        foreach (range(1, 3) as $i) {
            $quiz = Quiz::factory()->lobby()->create();
            $this->service->join($quiz, null, $token, 'Tobi');
        }

        $this->assertSame(3, $this->service->claimGuestScores($member, $token));
    }

    public function test_claiming_skips_a_quiz_they_already_played_signed_in(): void
    {
        $quiz = Quiz::factory()->lobby()->create();
        $member = Member::factory()->create();

        $this->service->join($quiz, $member, null, null);
        $guest = $this->service->join($quiz, null, 'other-device', 'Tobi');

        $claimed = $this->service->claimGuestScores($member, 'other-device');

        $this->assertSame(0, $claimed, 'The one-entry-per-quiz rule wins over the claim');
        $this->assertNull($guest->fresh()->member_id);
    }

    public function test_an_empty_token_claims_nothing(): void
    {
        $member = Member::factory()->create();
        QuizParticipant::factory()->create(['guest_token' => 'real-token']);

        $this->assertSame(0, $this->service->claimGuestScores($member, ''));
        $this->assertSame(0, $this->service->claimGuestScores($member, '   '));
    }

    // Host controls ------------------------------------------------------

    public function test_starting_a_quiz_gives_it_a_code_and_a_clock(): void
    {
        $quiz = Quiz::factory()->create(['status' => 'draft']);

        $this->service->start($quiz);

        $this->assertSame('running', $quiz->status);
        $this->assertNotNull($quiz->code);
        $this->assertNotNull($quiz->started_at);
    }

    public function test_pausing_and_resuming_banks_the_lost_time(): void
    {
        $quiz = $this->runningQuiz();

        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(4));
        $this->service->pause($quiz);

        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(34));
        $this->service->resume($quiz);

        $this->assertNull($quiz->paused_at);
        $this->assertSame(30000, $quiz->paused_ms, 'The 30s pause must not eat into the question');

        Carbon::setTestNow();
    }

    public function test_a_run_that_passes_its_end_closes_itself(): void
    {
        $quiz = $this->runningQuiz();

        // Two questions of 15s: the run is over by 40s.
        $this->service->refreshStatus($quiz, $quiz->started_at->copy()->addSeconds(40));

        $this->assertSame('finished', $quiz->fresh()->status);
        $this->assertNotNull($quiz->fresh()->finished_at);
    }

    public function test_a_quiz_still_in_play_is_left_alone(): void
    {
        $quiz = $this->runningQuiz();

        $this->service->refreshStatus($quiz, $quiz->started_at->copy()->addSeconds(5));

        $this->assertSame('running', $quiz->fresh()->status);
    }

    public function test_a_join_code_avoids_letters_that_get_misread(): void
    {
        foreach (range(1, 40) as $i) {
            $this->assertDoesNotMatchRegularExpression(
                '/[O0I1L5SB86G]/',
                Quiz::generateCode(),
                'Codes are read off a projector and typed on a phone',
            );
        }
    }
}
