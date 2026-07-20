<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * A congregation shares one NATed wifi address, so per-IP limits count the
 * whole room as one visitor. At the original settings the twenty-first person
 * to join was refused and most answers were rejected — failing for everyone at
 * once, mid-service, looking like the quiz was broken rather than like a limit.
 */
final class QuizRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('quiz-join:127.0.0.1');
    }

    private function runningQuiz(): Quiz
    {
        $quiz = Quiz::factory()->create([
            'status' => 'running', 'code' => 'QZ4KM', 'started_at' => now(),
            'seconds_per_question' => 600,
        ]);

        $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);
        QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Right']);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Wrong']);

        return $quiz->fresh(['questions.options']);
    }

    public function test_a_whole_congregation_can_join_from_one_address(): void
    {
        $this->runningQuiz();

        // All from 127.0.0.1, exactly as they would be behind church wifi.
        for ($i = 1; $i <= 120; $i++) {
            $response = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Player '.$i]);

            $this->assertSame(200, $response->status(), "Player {$i} was turned away");
        }
    }

    public function test_one_phone_polling_hard_does_not_use_up_everybody_elses_allowance(): void
    {
        $this->runningQuiz();
        $greedy = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Greedy'])->json('device_token');
        $quiet = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Quiet'])->json('device_token');

        // Well past one device's own allowance.
        for ($i = 0; $i < 95; $i++) {
            $this->getJson('/api/quiz/QZ4KM/state?device_token='.$greedy);
        }

        $this->assertSame(429, $this->getJson('/api/quiz/QZ4KM/state?device_token='.$greedy)->status());
        $this->assertSame(
            200,
            $this->getJson('/api/quiz/QZ4KM/state?device_token='.$quiet)->status(),
            'One phone must not be able to lock the room out',
        );
    }

    public function test_a_hundred_phones_can_all_answer_the_same_question(): void
    {
        $quiz = $this->runningQuiz();
        $option = $quiz->questions[0]->options->first();

        $tokens = [];
        for ($i = 1; $i <= 100; $i++) {
            $tokens[] = $this->postJson('/api/quiz/join', ['code' => 'QZ4KM', 'name' => 'Player '.$i])->json('device_token');
        }

        foreach ($tokens as $i => $token) {
            $response = $this->postJson('/api/quiz/QZ4KM/answer', [
                'device_token' => $token, 'option_id' => $option->id,
            ]);

            $this->assertNotSame(429, $response->status(), 'Answer '.($i + 1).' was throttled');
        }
    }
}
