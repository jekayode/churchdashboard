<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The web player is how most of the room joins in on a Sunday, because the app
 * is not on the App Store and asking a congregation to install a developer tool
 * would be worse than not running the quiz.
 *
 * Note what these tests cannot cover: Laravel skips CSRF checks under test, so
 * the browser's actual POST path is not exercised here. That was verified in a
 * real browser instead.
 */
final class QuizWebPlayerTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(string $status = 'lobby'): Quiz
    {
        $quiz = Quiz::factory()->create([
            'status' => $status,
            'code' => 'QZ4KM',
            'title' => 'LifeGroup Sunday Quiz',
            'started_at' => $status === 'running' ? now() : null,
        ]);

        $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);
        QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Joshua']);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Moses']);

        return $quiz;
    }

    public function test_the_player_page_opens_without_signing_in(): void
    {
        $this->quiz();

        $this->get('/quiz/QZ4KM')
            ->assertOk()
            ->assertSee('LifeGroup Sunday Quiz')
            ->assertSee('QZ4KM');
    }

    public function test_the_code_works_however_it_is_typed(): void
    {
        $this->quiz();

        // Typed off a projector, so case is whatever the keyboard did.
        $this->get('/quiz/qz4km')->assertOk();
    }

    public function test_an_unknown_code_is_a_404_not_a_crash(): void
    {
        $this->get('/quiz/NOPE1')->assertNotFound();
    }

    public function test_the_page_carries_a_csrf_token(): void
    {
        $this->quiz();

        // Sanctum treats same-origin requests as stateful, which brings CSRF
        // with it. Without this in the page, every guest POST is rejected.
        $this->get('/quiz/QZ4KM')->assertSee('name="csrf-token"', escape: false);
    }

    public function test_the_player_and_the_projector_are_different_pages(): void
    {
        $this->quiz();

        $player = $this->get('/quiz/QZ4KM');
        $screen = $this->get('/quiz/QZ4KM/screen');

        $player->assertOk()->assertSee('Your name');
        $screen->assertOk()->assertSee('Quiz code');
        $player->assertDontSee('Quiz code');
    }

    public function test_the_join_slide_stands_on_its_own_before_the_quiz_opens(): void
    {
        $quiz = $this->quiz('draft');

        /*
         * This goes on the screen ten minutes early, while the quiz is still a
         * draft, so it must not depend on the quiz having been opened and must
         * show nothing that changes while it is up.
         */
        $this->get('/quiz/QZ4KM/join')
            ->assertOk()
            ->assertSee('QZ4KM')
            ->assertSee('<svg', escape: false)
            ->assertSee('dash')
            ->assertDontSee('players');
    }

    public function test_the_short_url_serves_the_player_directly(): void
    {
        $this->quiz();

        // What the QR encodes. Served rather than redirected, so a scan costs
        // one request instead of two.
        $this->get('/q/QZ4KM')->assertOk()->assertSee('Your name');
    }

    public function test_the_short_url_is_shorter_than_the_long_one(): void
    {
        $quiz = $this->quiz();

        // Every character is more modules in the grid, and more modules on a
        // fixed screen width means smaller squares to read from the back.
        $this->assertLessThan(
            strlen(route('quiz.play', ['code' => $quiz->code])),
            strlen(\App\Services\Quiz\JoinQrCode::url($quiz)),
        );
    }

    public function test_the_player_page_never_carries_the_answer_in_its_markup(): void
    {
        $quiz = $this->quiz('running');

        /*
         * Questions arrive by fetch, not baked into the page, so the answer is
         * never one "view source" away. Asserted on the option text rather than
         * on "is_correct", which legitimately appears in the script that reads
         * the property once the reveal has released it.
         */
        $this->get('/quiz/QZ4KM')
            ->assertDontSee('Joshua')
            ->assertDontSee('Moses');
    }
}
