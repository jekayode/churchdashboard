<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizException;
use App\Services\Quiz\QuizService;
use App\Services\Quiz\QuizStatePresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The risky moment is a hundred people answering inside the same two seconds,
 * in front of the whole church. This exercises that shape rather than trusting
 * that it will be fine.
 */
final class QuizAtCongregationScaleTest extends TestCase
{
    use RefreshDatabase;

    private const ROOM = 100;

    public function test_a_hundred_players_answering_at_once_all_score_correctly(): void
    {
        $service = new QuizService;
        $quiz = $this->quizWithOneQuestion();
        $question = $quiz->questions->first();
        $right = $question->options->firstWhere('is_correct', true);
        $wrong = $question->options->firstWhere('is_correct', false);

        $players = collect(range(1, self::ROOM))->map(
            fn (int $i) => $service->join($quiz, null, null, 'Player '.$i)
        );

        // Everyone answers between one and three seconds in, which is what the
        // window actually looks like from the platform.
        $players->each(function ($participant, int $i) use ($service, $quiz, $right, $wrong): void {
            $service->submitAnswer(
                $participant,
                $i % 3 === 0 ? $wrong->id : $right->id,
                $quiz->started_at->copy()->addMilliseconds(1000 + ($i * 20)),
            );
        });

        $this->assertSame(self::ROOM, $question->answers()->count(), 'Every answer must land');

        $expectedCorrect = $players->keys()->reject(fn (int $i): bool => $i % 3 === 0)->count();
        $this->assertSame($expectedCorrect, $question->answers()->where('is_correct', true)->count());

        // Answering earlier must always be worth more, right across the field.
        $scores = $quiz->leaderboardQuery()->get();
        $this->assertSame(self::ROOM, $scores->count());
        $this->assertSame(
            $scores->pluck('score')->sortDesc()->values()->all(),
            $scores->pluck('score')->values()->all(),
            'The leaderboard must come back already ordered',
        );
    }

    public function test_the_leaderboard_stays_cheap_with_a_full_room(): void
    {
        $service = new QuizService;
        $quiz = $this->quizWithOneQuestion();
        collect(range(1, self::ROOM))->each(fn (int $i) => $service->join($quiz, null, null, 'Player '.$i));

        DB::enableQueryLog();
        $board = $service->leaderboard($quiz, 10);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(10, $board);
        $this->assertLessThanOrEqual(
            2,
            $queries,
            'The projector polls this every second and a half; it must not scan per player',
        );
    }

    public function test_a_retried_request_from_the_same_player_never_scores_twice(): void
    {
        $service = new QuizService;
        $quiz = $this->quizWithOneQuestion();
        $right = $quiz->questions->first()->options->firstWhere('is_correct', true);
        $participant = $service->join($quiz, null, null, 'Tobi');
        $at = $quiz->started_at->copy()->addSeconds(2);

        // A flaky phone on church wifi retries; the request may well arrive more
        // than once. Only the unique index stops that being free points.
        $accepted = 0;
        foreach (range(1, 5) as $attempt) {
            try {
                $service->submitAnswer($participant, $right->id, $at);
                $accepted++;
            } catch (QuizException) {
                // expected from the second attempt onwards
            }
        }

        $this->assertSame(1, $accepted);
        $this->assertSame(1, $participant->fresh()->answers()->count());
        // 2s into a 20s window: a tenth of the way through, so a twentieth off.
        $this->assertSame(950, $participant->fresh()->score, 'Scored once, at the time of the first attempt');
    }

    public function test_the_projector_payload_is_one_predictable_size_regardless_of_the_room(): void
    {
        $service = new QuizService;
        $presenter = new QuizStatePresenter($service);
        $quiz = $this->quizWithOneQuestion();
        collect(range(1, self::ROOM))->each(fn (int $i) => $service->join($quiz, null, null, 'Player '.$i));

        $payload = $presenter->forScreen($quiz, $quiz->started_at->copy()->addSeconds(2));

        // The board is capped, so the response does not grow with attendance.
        $this->assertCount(10, $payload['leaderboard']);
        $this->assertSame(self::ROOM, $payload['participant_count']);
    }

    private function quizWithOneQuestion(): Quiz
    {
        $quiz = Quiz::factory()->running(now())->create([
            'seconds_per_question' => 20,
            'reveal_seconds' => 5,
            'base_points' => 1000,
        ]);

        $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);
        QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Right']);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Wrong']);

        return $quiz->fresh(['questions.options']);
    }
}
