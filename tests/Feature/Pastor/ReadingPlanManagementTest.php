<?php

declare(strict_types=1);

namespace Tests\Feature\Pastor;

use App\Models\Branch;
use App\Models\ReadingDay;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReadingPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $pastor;

    private ReadingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->pastor = User::factory()->create(['email_verified_at' => now()]);
        $this->branch = Branch::factory()->create(['pastor_id' => $this->pastor->id, 'status' => 'active']);
        $this->pastor->assignRole('branch_pastor', $this->branch->id);

        $this->plan = ReadingPlan::factory()->annual()->create([
            'name' => 'Bible in a Year',
            'branch_id' => $this->branch->id,
            'attribution' => 'Courtesy of example.test',
        ]);

        foreach ([['0717', 'July 17', 198], ['0718', 'July 18', 199], ['0819', 'August 19', 231]] as [$md, $label, $number]) {
            ReadingDay::factory()->create([
                'reading_plan_id' => $this->plan->id,
                'day_number' => $number,
                'month_day' => $md,
                'label' => $label,
                'study_question_1' => 'Imported question for '.$label,
                'study_question_2' => null,
            ]);
        }
    }

    private function day(string $monthDay): ReadingDay
    {
        return $this->plan->days()->where('month_day', $monthDay)->firstOrFail();
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('pastor.reading-plans'))->assertRedirect();
    }

    public function test_church_member_cannot_reach_reading_plans(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $member->assignRole('church_member', $this->branch->id);

        $this->actingAs($member)
            ->get(route('pastor.reading-plans'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_pastor_sees_plans_with_rewrite_progress(): void
    {
        $this->day('0717')->update(['questions_updated_at' => now()]);

        $this->actingAs($this->pastor)
            ->get(route('pastor.reading-plans'))
            ->assertOk()
            ->assertSee('Bible in a Year')
            ->assertSee('1/3');
    }

    public function test_day_list_shows_progress_and_filters_by_status(): void
    {
        $this->day('0717')->update(['questions_updated_at' => now()]);

        $this->actingAs($this->pastor);

        $this->get(route('pastor.reading-plans.days', $this->plan))
            ->assertOk()
            ->assertSee('1 of 3 days rewritten')
            ->assertSee('July 17')
            ->assertSee('July 18');

        // Only days still using the imported text.
        $this->get(route('pastor.reading-plans.days', $this->plan).'?status=todo')
            ->assertOk()
            ->assertSee('July 18')
            ->assertDontSee('July 17');
    }

    public function test_day_list_filters_by_month(): void
    {
        $this->actingAs($this->pastor)
            ->get(route('pastor.reading-plans.days', $this->plan).'?month=08')
            ->assertOk()
            ->assertSee('August 19')
            ->assertDontSee('July 18');
    }

    public function test_pastor_can_rewrite_the_questions(): void
    {
        $day = $this->day('0718');

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$this->plan, $day]), [
                'study_question_1' => 'Where are your roots drawing from today?',
                'study_question_2' => 'Who can you encourage this week?',
            ])
            ->assertRedirect(route('pastor.reading-plans.days.edit', [$this->plan, $day]));

        $day->refresh();

        $this->assertSame('Where are your roots drawing from today?', $day->study_question_1);
        $this->assertTrue($day->hasOwnQuestions());
    }

    public function test_editing_only_a_reference_does_not_mark_the_day_rewritten(): void
    {
        $day = $this->day('0718');

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$this->plan, $day]), [
                'study_question_1' => $day->study_question_1,
                'old_testament' => 'PSALM 23:1-6',
            ])->assertRedirect();

        $day->refresh();

        $this->assertSame('PSALM 23:1-6', $day->old_testament);
        // Still the imported wording, so progress must not count it.
        $this->assertFalse($day->hasOwnQuestions());
    }

    public function test_save_and_next_moves_to_the_following_day(): void
    {
        $day = $this->day('0717');
        $next = $this->day('0718');

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$this->plan, $day]), [
                'study_question_1' => 'Rewritten',
                'save_and_next' => '1',
            ])
            ->assertRedirect(route('pastor.reading-plans.days.edit', [$this->plan, $next]));
    }

    public function test_an_unresolvable_reference_warns_but_still_saves(): void
    {
        $day = $this->day('0718');

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$this->plan, $day]), [
                'study_question_1' => $day->study_question_1,
                'old_testament' => 'Book of Nonsense 3',
            ])
            ->assertRedirect()
            ->assertSessionHas('warning');

        // Saved regardless: the pastor may have meant it.
        $this->assertSame('Book of Nonsense 3', $day->refresh()->old_testament);
    }

    public function test_valid_references_produce_no_warning(): void
    {
        $day = $this->day('0718');

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$this->plan, $day]), [
                'study_question_1' => $day->study_question_1,
                'old_testament' => '1 CHRONICLES 24:1-26:11',
            ])
            ->assertRedirect()
            ->assertSessionMissing('warning');
    }

    public function test_pastor_can_update_plan_settings_and_clear_attribution(): void
    {
        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.update', $this->plan), [
                'name' => 'LifePointe Bible Plan',
                'attribution' => '',
                'is_published' => '1',
            ])
            ->assertRedirect(route('pastor.reading-plans'));

        $this->plan->refresh();

        $this->assertSame('LifePointe Bible Plan', $this->plan->name);
        // Once the content is their own, the credit line can go.
        $this->assertNull($this->plan->attribution);
    }

    public function test_making_a_plan_default_clears_the_previous_one(): void
    {
        $other = ReadingPlan::factory()->create(['is_default' => true]);

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.update', $this->plan), [
                'name' => $this->plan->name,
                'is_default' => '1',
            ])->assertRedirect();

        $this->assertFalse($other->refresh()->is_default);
        $this->assertTrue($this->plan->refresh()->is_default);
    }

    public function test_pastor_cannot_edit_another_branchs_plan(): void
    {
        $otherBranch = Branch::factory()->create();
        $plan = ReadingPlan::factory()->create(['branch_id' => $otherBranch->id]);
        $day = ReadingDay::factory()->create(['reading_plan_id' => $plan->id, 'day_number' => 1]);

        $this->actingAs($this->pastor);

        $this->get(route('pastor.reading-plans.days', $plan))->assertForbidden();
        $this->put(route('pastor.reading-plans.days.update', [$plan, $day]), ['study_question_1' => 'x'])->assertForbidden();
    }

    public function test_pastor_cannot_rewrite_a_network_wide_plan(): void
    {
        // Shared plans belong to super admins so one branch can't change them for all.
        $shared = ReadingPlan::factory()->create(['branch_id' => null]);
        $day = ReadingDay::factory()->create(['reading_plan_id' => $shared->id, 'day_number' => 1]);

        $this->actingAs($this->pastor)
            ->put(route('pastor.reading-plans.days.update', [$shared, $day]), ['study_question_1' => 'x'])
            ->assertForbidden();
    }

    public function test_a_day_from_another_plan_is_not_reachable(): void
    {
        $otherPlan = ReadingPlan::factory()->create(['branch_id' => $this->branch->id]);
        $foreignDay = ReadingDay::factory()->create(['reading_plan_id' => $otherPlan->id, 'day_number' => 1]);

        $this->actingAs($this->pastor)
            ->get(route('pastor.reading-plans.days.edit', [$this->plan, $foreignDay]))
            ->assertNotFound();
    }

    public function test_sidebar_exposes_the_reading_plans_link(): void
    {
        $this->actingAs($this->pastor)
            ->get(route('pastor.reading-plans'))
            ->assertOk()
            ->assertSee(route('pastor.reading-plans'), false);
    }
}
