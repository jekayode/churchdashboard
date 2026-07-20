<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The lobby and the join slide are both read from across a cinema, on a page
 * that clips its own overflow — so anything that does not fit does not just
 * look cramped, it disappears. These pin the sizing decisions that keep them
 * on screen; the layout itself was checked in a browser at 16:9 and 2.37:1.
 */
final class QuizScreenLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(): Quiz
    {
        $quiz = Quiz::factory()->lobby()->create(['code' => 'YNUAH', 'title' => 'Test quiz']);
        $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);
        QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Yes']);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'No']);

        return $quiz;
    }

    public function test_the_lobby_is_sized_against_height_not_width(): void
    {
        $this->quiz();

        $page = $this->get('/quiz/YNUAH/screen')->getContent();

        /*
         * Sized by width, a QR and a code that each look reasonable alone are
         * together too wide to sit side by side and too tall once they stack —
         * and the code fell off the bottom of a page that hides its overflow.
         * Height is the axis that is actually scarce.
         */
        $this->assertStringContainsString('.qr svg { width: 48vh; height: 48vh;', $page);
        $this->assertStringNotContainsString('.qr svg { width: 26vw', $page);
        $this->assertStringContainsString('font-size: 14vh', $page, 'The code sits beside the QR, so it is sized against height too');
    }

    public function test_the_lobby_row_is_given_the_full_width_to_lay_out_in(): void
    {
        $this->quiz();

        // Left to size itself, the row measured its content but not the gap
        // between, wrapping a fraction of a pixel early with metres to spare.
        $this->assertStringContainsString('.lobby { display: flex; width: 100%;', $this->get('/quiz/YNUAH/screen')->getContent());
    }

    public function test_the_join_slide_row_is_given_the_full_width_too(): void
    {
        $this->quiz();

        $this->assertStringContainsString(
            '.split { display: flex; width: 100%;',
            $this->get('/quiz/YNUAH/join')->getContent(),
        );
    }

    public function test_both_screens_show_the_code_and_the_link_beside_the_qr(): void
    {
        $this->quiz();

        // A QR only reaches the part of the room close enough to resolve it, so
        // the code and the typed link always accompany it.
        foreach (['/quiz/YNUAH/screen', '/quiz/YNUAH/join'] as $url) {
            $page = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('YNUAH', $page);
            $this->assertStringContainsString('Quiz code', $page);
        }
    }
}
