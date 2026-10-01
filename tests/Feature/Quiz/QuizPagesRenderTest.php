<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Branch;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Blade mistake only shows itself when the page is rendered, and these three
 * pages are all first opened minutes before a service starts.
 */
final class QuizPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    private function pastor(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('branch_pastor');
        $branch = Branch::factory()->create(['pastor_id' => $user->id, 'status' => 'active']);
        $user->assignRole('branch_pastor', $branch->id);

        return $user->fresh();
    }

    private function quizFor(User $pastor, string $status = 'draft'): Quiz
    {
        $quiz = Quiz::factory()->create([
            'branch_id' => $pastor->getActiveBranchId(),
            'status' => $status,
            'code' => 'QZ4KM',
        ]);

        $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1, 'text' => 'Who led Israel?']);
        QuizOption::factory()->correct()->create(['quiz_question_id' => $question->id, 'position' => 1, 'text' => 'Joshua']);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'position' => 2, 'text' => 'Moses']);

        return $quiz;
    }

    public function test_the_new_quiz_form_renders(): void
    {
        $this->actingAs($this->pastor())->get(route('pastor.quizzes.create'))->assertOk();
    }

    public function test_the_question_editor_renders_with_existing_questions(): void
    {
        $pastor = $this->pastor();
        $quiz = $this->quizFor($pastor);

        $this->actingAs($pastor)
            ->get(route('pastor.quizzes.questions', $quiz))
            ->assertOk()
            ->assertSee('Who led Israel?');
    }

    public function test_the_host_console_renders(): void
    {
        $pastor = $this->pastor();
        $quiz = $this->quizFor($pastor, 'lobby');

        $this->actingAs($pastor)
            ->get(route('pastor.quizzes.host', $quiz))
            ->assertOk()
            ->assertSee('Start the quiz');
    }

    public function test_the_projector_screen_renders_without_signing_in(): void
    {
        $this->quizFor($this->pastor(), 'lobby');

        // No auth: this is opened on the machine driving the screen.
        $this->get('/quiz/QZ4KM/screen')
            ->assertOk()
            ->assertSee('QZ4KM')
            /*
             * The QR and join link are rendered into the page rather than
             * fetched, so the projector never depends on a request succeeding
             * to show people how to get in. Asserted via the link rather than
             * the markup, because the QR is embedded inside a script constant
             * where Blade escapes its angle brackets.
             */
            ->assertSee('\u003Csvg', escape: false);
    }

    public function test_the_independence_theme_dresses_the_screen_slide_and_phones(): void
    {
        $quiz = $this->quizFor($this->pastor(), 'lobby');
        $quiz->update(['theme' => 'nigeria']);

        foreach (['/quiz/QZ4KM/screen', '/quiz/QZ4KM/join', '/quiz/QZ4KM'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('class="theme-nigeria"', false)
                ->assertSee('class="flag-band"', false);
        }

        $this->get('/quiz/QZ4KM/join')->assertSee('Happy Independence · Nigeria @ '.(now()->year - 1960));
        $this->get('/quiz/QZ4KM/screen')->assertSee('Happy Independence');
    }

    public function test_a_standard_quiz_has_no_flag(): void
    {
        $this->quizFor($this->pastor(), 'lobby');

        foreach (['/quiz/QZ4KM/screen', '/quiz/QZ4KM/join', '/quiz/QZ4KM'] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('class="theme-standard"', false)
                ->assertDontSee('class="flag-band"', false)
                ->assertDontSee('Happy Independence');
        }
    }

    public function test_the_finish_carries_its_medals_and_confetti_on_screen_and_phone(): void
    {
        $this->quizFor($this->pastor(), 'lobby');

        foreach (['/quiz/QZ4KM/screen', '/quiz/QZ4KM'] as $page) {
            $this->get($page)
                ->assertSee('window.quizCelebration', false)
                ->assertSee('celebrate.medal(', false)
                ->assertSee('celebrate.confetti(', false);
        }
    }

    public function test_an_unknown_code_on_the_projector_is_a_404_not_a_crash(): void
    {
        $this->get('/quiz/NOPE1/screen')->assertNotFound();
    }
}
