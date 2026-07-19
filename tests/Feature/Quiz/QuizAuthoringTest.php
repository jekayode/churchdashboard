<?php

declare(strict_types=1);

namespace Tests\Feature\Quiz;

use App\Models\Branch;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QuizAuthoringTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function questionPayload(): array
    {
        return [
            'questions' => [
                [
                    'text' => 'Who led Israel across the Jordan?',
                    'correct' => 1,
                    'options' => [['text' => 'Moses'], ['text' => 'Joshua'], ['text' => 'Caleb']],
                ],
            ],
        ];
    }

    public function test_a_pastor_can_create_a_quiz(): void
    {
        $pastor = $this->pastor();

        $response = $this->actingAs($pastor)->post(route('pastor.quizzes.store'), [
            'title' => 'LifeGroup Sunday Quiz',
            'seconds_per_question' => 20,
            'reveal_seconds' => 6,
            'base_points' => 1000,
            'allow_guests' => '1',
        ]);

        $quiz = Quiz::firstWhere('title', 'LifeGroup Sunday Quiz');
        $this->assertNotNull($quiz);
        $this->assertSame('draft', $quiz->status);
        $response->assertRedirect(route('pastor.quizzes.questions', $quiz));
    }

    public function test_the_quizzes_page_is_reachable_from_the_sidebar(): void
    {
        $response = $this->actingAs($this->pastor())->get(route('pastor.quizzes'));

        $response->assertOk();
        $response->assertSee(route('pastor.quizzes'), escape: false);
    }

    public function test_questions_and_the_marked_answer_are_saved(): void
    {
        $pastor = $this->pastor();
        $quiz = Quiz::factory()->create(['branch_id' => $pastor->getActiveBranchId()]);

        $this->actingAs($pastor)
            ->put(route('pastor.quizzes.questions.update', $quiz), $this->questionPayload())
            ->assertRedirect(route('pastor.quizzes'));

        $question = $quiz->fresh()->questions()->with('options')->first();
        $this->assertSame('Who led Israel across the Jordan?', $question->text);
        $this->assertCount(3, $question->options);
        $this->assertSame('Joshua', $question->correctOption()->text);
    }

    public function test_saving_questions_replaces_the_previous_set(): void
    {
        $pastor = $this->pastor();
        $quiz = Quiz::factory()->create(['branch_id' => $pastor->getActiveBranchId()]);
        QuizQuestion::factory()->count(3)->sequence(
            ['position' => 1], ['position' => 2], ['position' => 3],
        )->create(['quiz_id' => $quiz->id]);

        $this->actingAs($pastor)->put(route('pastor.quizzes.questions.update', $quiz), $this->questionPayload());

        $this->assertSame(1, $quiz->fresh()->questions()->count());
    }

    public function test_a_question_with_no_marked_answer_is_rejected(): void
    {
        $pastor = $this->pastor();
        $quiz = Quiz::factory()->create(['branch_id' => $pastor->getActiveBranchId()]);

        $response = $this->actingAs($pastor)->put(route('pastor.quizzes.questions.update', $quiz), [
            'questions' => [[
                'text' => 'A question',
                'correct' => 5, // points at an option that does not exist
                'options' => [['text' => 'One'], ['text' => 'Two']],
            ]],
        ]);

        $response->assertSessionHasErrors('questions.0.correct');
        $this->assertSame(0, $quiz->fresh()->questions()->count());
    }

    public function test_a_question_needs_at_least_two_answers(): void
    {
        $pastor = $this->pastor();
        $quiz = Quiz::factory()->create(['branch_id' => $pastor->getActiveBranchId()]);

        $this->actingAs($pastor)->put(route('pastor.quizzes.questions.update', $quiz), [
            'questions' => [['text' => 'A question', 'correct' => 0, 'options' => [['text' => 'Only one']]]],
        ])->assertSessionHasErrors('questions.0.options');
    }

    public function test_questions_are_locked_once_the_quiz_has_been_opened(): void
    {
        $pastor = $this->pastor();
        $quiz = Quiz::factory()->lobby()->create(['branch_id' => $pastor->getActiveBranchId()]);
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);

        $this->actingAs($pastor)
            ->put(route('pastor.quizzes.questions.update', $quiz), $this->questionPayload())
            ->assertSessionHas('error');

        $this->assertSame(1, $quiz->fresh()->questions()->count(), 'Scores would stop meaning anything');
    }

    public function test_a_pastor_cannot_touch_another_branchs_quiz(): void
    {
        $quiz = Quiz::factory()->create(['branch_id' => Branch::factory()->create()->id]);

        $this->actingAs($this->pastor())
            ->put(route('pastor.quizzes.questions.update', $quiz), $this->questionPayload())
            ->assertForbidden();
    }

    public function test_an_ordinary_member_cannot_write_quizzes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('church_member');

        $response = $this->actingAs($user)->get(route('pastor.quizzes'));

        $this->assertContains($response->status(), [302, 403], 'A member must not reach the quiz admin');
        $this->assertNotSame(200, $response->status());
    }
}
