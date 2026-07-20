<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\Quiz\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class QuizPlayApiTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(string $status = 'lobby'): Quiz
    {
        $quiz = Quiz::factory()->create([
            'status' => $status,
            'code' => 'QZ4KM',
            'seconds_per_question' => 10,
            'reveal_seconds' => 5,
            'started_at' => $status === 'running' ? now() : null,
        ]);

        foreach ([1, 2] as $position) {
            $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => $position]);
            QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Right']);
            QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Wrong']);
        }

        return $quiz->fresh(['questions.options']);
    }

    // Joining ------------------------------------------------------------

    public function test_a_guest_joins_with_a_code_and_a_name(): void
    {
        $this->quiz();

        $response = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);

        $response->assertOk()
            ->assertJsonPath('display_name', 'Tobi')
            ->assertJsonPath('is_guest', true);

        $this->assertNotEmpty($response->json('device_token'), 'Without a token the score can never be claimed');
    }

    public function test_the_code_is_accepted_however_it_is_typed(): void
    {
        $this->quiz();

        $this->postJson('/api/quiz/join', ['code' => 'qz4km', 'name' => 'Tobi'])->assertOk();
    }

    public function test_an_unknown_code_is_a_clear_404(): void
    {
        $this->postJson('/api/quiz/join', ['code' => 'ZZZZZ', 'name' => 'Tobi'])->assertNotFound();
    }

    public function test_a_signed_in_member_is_recognised_rather_than_treated_as_a_guest(): void
    {
        $this->quiz();
        $user = User::factory()->create();
        $member = Member::factory()->create(['first_name' => 'Emmanuel', 'surname' => 'Joseph']);
        $user->member()->save($member);
        Sanctum::actingAs($user);

        $this->postJson('/api/quiz/join', ['code' => 'QZ4KM'])
            ->assertOk()
            ->assertJsonPath('is_guest', false)
            ->assertJsonPath('display_name', 'Emmanuel Joseph');
    }

    public function test_an_unacceptable_name_is_refused_with_a_reason(): void
    {
        $this->quiz();

        $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'sh1t'])
            ->assertStatus(422)
            ->assertJsonPath('reason', 'name_rejected');
    }

    // The answer must not be readable before the question closes ----------

    public function test_the_correct_answer_is_not_in_the_payload_while_the_question_is_open(): void
    {
        $quiz = $this->quiz('running');
        $join = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);

        $state = $this->getJson('/api/quiz/QZ4KM/state?device_token='.$join->json('device_token'));

        $state->assertOk()->assertJsonPath('state.phase', 'question');

        foreach ($state->json('question.options') as $option) {
            $this->assertArrayNotHasKey(
                'is_correct',
                $option,
                'Otherwise winning is a matter of reading the state endpoint instead of playing',
            );
        }

        $this->assertNull($state->json('answer_counts'), 'A live tally is a strong hint');
    }

    public function test_the_projector_endpoint_leaks_nothing_either(): void
    {
        $this->quiz('running');

        // Open to anyone with the code, so it is the easiest thing for a player
        // to poll from their own phone.
        $state = $this->getJson('/quiz/QZ4KM/screen/state');

        $state->assertOk();
        foreach ($state->json('question.options') as $option) {
            $this->assertArrayNotHasKey('is_correct', $option);
        }
    }

    public function test_the_answer_appears_once_the_question_has_closed(): void
    {
        $quiz = $this->quiz('running');
        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(11));

        $state = $this->getJson('/quiz/QZ4KM/screen/state');

        $state->assertOk()->assertJsonPath('state.phase', 'reveal');
        $flags = array_column($state->json('question.options'), 'is_correct');
        $this->assertContains(true, $flags, 'The room has to be shown the answer');

        Carbon::setTestNow();
    }

    // Answering ----------------------------------------------------------

    public function test_a_correct_answer_scores(): void
    {
        $quiz = $this->quiz('running');
        $join = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);
        $correct = $quiz->questions[0]->options->firstWhere('is_correct', true);

        $response = $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $join->json('device_token'),
            'option_id' => $correct->id,
        ]);

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('me.score'));
    }

    public function test_answering_twice_is_refused(): void
    {
        $quiz = $this->quiz('running');
        $join = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);
        $token = $join->json('device_token');
        $options = $quiz->questions[0]->options;

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $token, 'option_id' => $options->firstWhere('is_correct', false)->id,
        ])->assertOk();

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $token, 'option_id' => $options->firstWhere('is_correct', true)->id,
        ])->assertStatus(409)->assertJsonPath('reason', 'already_answered');
    }

    public function test_an_answer_after_the_bell_is_refused(): void
    {
        $quiz = $this->quiz('running');
        $join = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);

        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(11));

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $join->json('device_token'),
            'option_id' => $quiz->questions[0]->options->first()->id,
        ])->assertStatus(409);

        Carbon::setTestNow();
    }

    public function test_answering_without_having_joined_is_refused(): void
    {
        $quiz = $this->quiz('running');

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => 'not-a-real-token',
            'option_id' => $quiz->questions[0]->options->first()->id,
        ])->assertForbidden();
    }

    public function test_a_player_cannot_answer_for_somebody_else(): void
    {
        $quiz = $this->quiz('running');
        $mine = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);
        $theirs = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Grace']);

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $mine->json('device_token'),
            'option_id' => $quiz->questions[0]->options->firstWhere('is_correct', true)->id,
        ])->assertOk();

        // Their score is untouched: the token, not the participant id, decides
        // whose answer this is.
        $theirState = $this->getJson('/api/quiz/QZ4KM/state?device_token='.$theirs->json('device_token'));
        $this->assertSame(0, $theirState->json('me.score'));
    }

    // Guest to member ----------------------------------------------------

    public function test_a_guest_score_is_claimed_after_signing_in(): void
    {
        $quiz = $this->quiz('running');
        $join = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);
        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $join->json('device_token'),
            'option_id' => $quiz->questions[0]->options->firstWhere('is_correct', true)->id,
        ]);

        $user = User::factory()->create();
        $user->member()->save(Member::factory()->make());
        Sanctum::actingAs($user->fresh());

        $this->postJson('/api/me/quiz/claim', ['device_token' => $join->json('device_token')])
            ->assertOk()
            ->assertJsonPath('claimed', 1);

        (new QuizService)->finish($quiz);

        $history = $this->getJson('/api/me/quiz/history');
        $history->assertOk()->assertJsonPath('data.0.title', $quiz->title);
        $this->assertGreaterThan(0, $history->json('data.0.score'));
    }

    // Finding a live quiz without typing the code ------------------------

    private function signedInMemberAtBranch(int $branchId): User
    {
        $user = User::factory()->create();
        $user->member()->save(Member::factory()->make(['branch_id' => $branchId]));
        Sanctum::actingAs($user->fresh());

        return $user->fresh();
    }

    public function test_a_member_sees_a_quiz_that_is_open_at_their_branch(): void
    {
        $quiz = $this->quiz('lobby');
        $this->signedInMemberAtBranch($quiz->branch_id);

        $this->getJson('/api/me/quiz/active')
            ->assertOk()
            ->assertJsonPath('data.code', 'QZ4KM')
            ->assertJsonPath('data.status', 'lobby')
            ->assertJsonPath('data.joined', false);
    }

    public function test_a_draft_quiz_is_not_advertised(): void
    {
        $quiz = $this->quiz('draft');
        $this->signedInMemberAtBranch($quiz->branch_id);

        // It may still have half-written questions in it, and the pastor has not
        // said the room is ready.
        $this->getJson('/api/me/quiz/active')->assertOk()->assertJsonPath('data', null);
    }

    public function test_a_finished_quiz_is_not_advertised(): void
    {
        $quiz = $this->quiz('lobby');
        (new QuizService)->finish($quiz);
        $this->signedInMemberAtBranch($quiz->branch_id);

        $this->getJson('/api/me/quiz/active')->assertOk()->assertJsonPath('data', null);
    }

    public function test_a_run_that_has_passed_its_end_is_not_advertised(): void
    {
        $quiz = $this->quiz('running');
        $this->signedInMemberAtBranch($quiz->branch_id);

        // Two questions of 15s: over by 40s, but nothing has looked at it yet.
        Carbon::setTestNow($quiz->started_at->copy()->addSeconds(40));

        $this->getJson('/api/me/quiz/active')->assertOk()->assertJsonPath('data', null);

        Carbon::setTestNow();
    }

    public function test_a_member_does_not_see_another_branchs_quiz(): void
    {
        $this->quiz('lobby');
        $elsewhere = \App\Models\Branch::factory()->create();
        $this->signedInMemberAtBranch($elsewhere->id);

        $this->getJson('/api/me/quiz/active')->assertOk()->assertJsonPath('data', null);
    }

    public function test_the_banner_knows_when_they_are_already_in(): void
    {
        $quiz = $this->quiz('lobby');
        $user = $this->signedInMemberAtBranch($quiz->branch_id);
        (new QuizService)->join($quiz, $user->member, null, null);

        // So a phone that locked halfway through is offered "Rejoin".
        $this->getJson('/api/me/quiz/active')->assertOk()->assertJsonPath('data.joined', true);
    }

    public function test_finding_a_live_quiz_needs_an_account(): void
    {
        $this->quiz('lobby');

        // A guest still reads the code off the wall — that asymmetry is the
        // reason to sign in.
        $this->getJson('/api/me/quiz/active')->assertUnauthorized();
    }

    // The wait after answering ------------------------------------------

    public function test_how_many_have_answered_is_visible_but_not_how_they_answered(): void
    {
        $quiz = $this->quiz('running');
        $first = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Tobi']);
        $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Grace']);

        $this->postJson('/api/quiz/QZ4KM/answer', [
            'device_token' => $first->json('device_token'),
            'option_id' => $quiz->questions[0]->options->firstWhere('is_correct', true)->id,
        ])->assertOk();

        $state = $this->getJson('/api/quiz/QZ4KM/state?device_token='.$first->json('device_token'));

        // The count gives the wait something to watch; the split would be a hint.
        $state->assertOk()->assertJsonPath('answered_count', 1);
        $this->assertNull($state->json('answer_counts'));
    }

    public function test_history_needs_an_account(): void
    {
        $this->getJson('/api/me/quiz/history')->assertUnauthorized();
    }

    public function test_history_only_shows_finished_quizzes(): void
    {
        $quiz = $this->quiz('running');
        $user = User::factory()->create();
        $member = Member::factory()->make();
        $user->member()->save($member);
        Sanctum::actingAs($user->fresh());

        (new QuizService)->join($quiz, $user->fresh()->member, null, null);

        $this->getJson('/api/me/quiz/history')->assertOk()->assertJsonCount(0, 'data');
    }
}
