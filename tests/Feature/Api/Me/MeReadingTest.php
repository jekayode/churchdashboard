<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberReadingProgress;
use App\Models\ReadingDay;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Services\ReadingStreakService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeReadingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Member $member;

    private Branch $branch;

    private ReadingPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('church_member', $this->branch->id);
        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        // A small annual plan keyed by month-day, like the real one.
        $this->plan = ReadingPlan::factory()->annual()->create([
            'name' => 'Bible in a Year',
            'is_default' => true,
            'length_days' => 3,
            'attribution' => 'Courtesy of example.test',
        ]);

        foreach ([['0717', 'July 17', 198], ['0718', 'July 18', 199], ['0719', 'July 19', 200]] as [$md, $label, $number]) {
            ReadingDay::factory()->create([
                'reading_plan_id' => $this->plan->id,
                'day_number' => $number,
                'month_day' => $md,
                'label' => $label,
                'old_testament' => '1 CHRONICLES 24:1-26:11',
                'new_testament' => 'ROMANS 4:1-12',
                'psalm' => 'PSALM 13:1-6',
                'proverbs' => 'PROVERBS 19:15-16',
                'study_question_1' => 'Question one',
                'study_question_2' => 'Question two',
            ]);
        }
    }

    private function dayFor(string $monthDay): ReadingDay
    {
        return $this->plan->days()->where('month_day', $monthDay)->firstOrFail();
    }

    public function test_guest_cannot_read(): void
    {
        $this->getJson('/api/me/reading/today')->assertUnauthorized();
    }

    public function test_today_returns_the_matching_day_with_readings_and_questions(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/reading/today?date=2026-07-18')->assertOk();

        $response->assertJsonPath('data.label', 'July 18')
            ->assertJsonPath('meta.plan.name', 'Bible in a Year')
            ->assertJsonPath('meta.plan.attribution', 'Courtesy of example.test');

        // The four reading groups stay separate for the app's sections.
        $this->assertCount(4, $response->json('data.references'));
        $this->assertSame('Old Testament', $response->json('data.references.0.group'));
        $this->assertCount(2, $response->json('data.study_questions'));
    }

    public function test_annual_plan_serves_the_same_day_in_any_year(): void
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/reading/today?date=2026-07-18')->assertJsonPath('data.label', 'July 18');
        // A far-future date is clamped to today, so query the day list instead.
        $this->assertSame('July 18', $this->plan->dayForDate(new \DateTime('2031-07-18'))->label);
    }

    public function test_completing_a_reading_starts_a_streak(): void
    {
        Sanctum::actingAs($this->user);
        $day = $this->dayFor('0718');

        $this->postJson("/api/me/reading/days/{$day->id}/complete", ['date' => '2026-07-18'])
            ->assertOk()
            ->assertJsonPath('data.streak.current', 1)
            ->assertJsonPath('data.streak.completed_today', true);

        // whereDate, because SQLite keeps the time component that MySQL's DATE
        // column truncates away.
        $this->assertTrue(
            MemberReadingProgress::query()
                ->where('member_id', $this->member->id)
                ->where('reading_day_id', $day->id)
                ->whereDate('completed_on', '2026-07-18')
                ->exists(),
        );
    }

    public function test_completing_twice_does_not_duplicate(): void
    {
        Sanctum::actingAs($this->user);
        $day = $this->dayFor('0718');

        $this->postJson("/api/me/reading/days/{$day->id}/complete", ['date' => '2026-07-18'])->assertOk();
        $this->postJson("/api/me/reading/days/{$day->id}/complete", ['date' => '2026-07-18'])->assertOk();

        $this->assertDatabaseCount('member_reading_progress', 1);
    }

    public function test_a_reading_can_be_unmarked(): void
    {
        Sanctum::actingAs($this->user);
        $day = $this->dayFor('0718');

        $this->postJson("/api/me/reading/days/{$day->id}/complete", ['date' => '2026-07-18'])->assertOk();
        $this->deleteJson("/api/me/reading/days/{$day->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.streak.current', 0);

        $this->assertDatabaseCount('member_reading_progress', 0);
    }

    public function test_plan_list_flags_done_and_today(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/me/reading/days/'.$this->dayFor('0717')->id.'/complete', ['date' => '2026-07-17'])->assertOk();

        $days = collect($this->getJson('/api/me/reading/plan?date=2026-07-18')->assertOk()->json('data'));

        $seventeenth = $days->firstWhere('label', 'July 17');
        $eighteenth = $days->firstWhere('label', 'July 18');

        $this->assertTrue($seventeenth['is_done']);
        $this->assertFalse($seventeenth['is_today']);
        $this->assertFalse($eighteenth['is_done']);
        $this->assertTrue($eighteenth['is_today']);
    }

    public function test_progress_does_not_leak_between_members(): void
    {
        $other = User::factory()->create();
        $other->assignRole('church_member', $this->branch->id);
        $otherMember = Member::factory()->create(['user_id' => $other->id, 'branch_id' => $this->branch->id]);

        MemberReadingProgress::create([
            'member_id' => $otherMember->id,
            'reading_day_id' => $this->dayFor('0718')->id,
            'reading_plan_id' => $this->plan->id,
            'completed_on' => '2026-07-18',
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/reading/streak?date=2026-07-18')
            ->assertOk()
            ->assertJsonPath('data.current', 0)
            ->assertJsonPath('data.total_days', 0);
    }

    public function test_member_can_enrol_in_a_plan(): void
    {
        $other = ReadingPlan::factory()->create(['name' => 'Rooted']);
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/reading/plans/{$other->id}/enrol")->assertOk();

        $this->assertDatabaseHas('member_plan_enrolments', [
            'member_id' => $this->member->id,
            'reading_plan_id' => $other->id,
            'is_active' => true,
        ]);
    }

    public function test_enrolling_deactivates_the_previous_plan(): void
    {
        $first = ReadingPlan::factory()->create(['name' => 'First']);
        $second = ReadingPlan::factory()->create(['name' => 'Second']);

        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/reading/plans/{$first->id}/enrol")->assertOk();
        $this->postJson("/api/me/reading/plans/{$second->id}/enrol")->assertOk();

        $this->assertDatabaseHas('member_plan_enrolments', ['reading_plan_id' => $first->id, 'is_active' => false]);
        $this->assertDatabaseHas('member_plan_enrolments', ['reading_plan_id' => $second->id, 'is_active' => true]);
    }

    public function test_cannot_enrol_in_an_unpublished_plan(): void
    {
        $draft = ReadingPlan::factory()->unpublished()->create();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/reading/plans/{$draft->id}/enrol")->assertNotFound();
    }

    public function test_day_can_include_scripture_text_on_request(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'rest.api.bible/*' => \Illuminate\Support\Facades\Http::response([
                'data' => [
                    'reference' => 'Psalms 13:1-6',
                    'content' => '[1] O Lord, how long will you forget me?',
                    'copyright' => 'Holy Bible, New Living Translation',
                ],
            ]),
        ]);
        config()->set('bible.key', 'test-key');

        Sanctum::actingAs($this->user);
        $day = $this->dayFor('0718');

        $response = $this->getJson("/api/me/reading/days/{$day->id}?with_text=1")->assertOk();

        $this->assertNotNull($response->json('data.references.0.text'));
        // Licensed translations must carry their notice.
        $this->assertSame('Holy Bible, New Living Translation', $response->json('data.references.0.copyright'));
    }

    public function test_day_omits_scripture_text_by_default(): void
    {
        \Illuminate\Support\Facades\Http::fake();
        Sanctum::actingAs($this->user);

        $day = $this->dayFor('0718');

        $this->getJson("/api/me/reading/days/{$day->id}")->assertOk();

        // Browsing must not spend API calls.
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    /**
     * Streak maths, exercised directly so the edge cases are explicit.
     */
    public function test_streak_counts_consecutive_days(): void
    {
        $this->recordDays(['2026-07-16', '2026-07-17', '2026-07-18']);

        $summary = app(ReadingStreakService::class)->summary($this->member, CarbonImmutable::parse('2026-07-18'));

        $this->assertSame(3, $summary['current']);
        $this->assertSame(3, $summary['longest']);
        $this->assertTrue($summary['completed_today']);
    }

    public function test_streak_survives_until_today_is_over(): void
    {
        // Read yesterday, not yet today: the streak should still stand.
        $this->recordDays(['2026-07-16', '2026-07-17']);

        $summary = app(ReadingStreakService::class)->summary($this->member, CarbonImmutable::parse('2026-07-18'));

        $this->assertSame(2, $summary['current']);
        $this->assertFalse($summary['completed_today']);
    }

    public function test_streak_breaks_after_a_missed_day(): void
    {
        // Nothing on the 17th.
        $this->recordDays(['2026-07-15', '2026-07-16']);

        $summary = app(ReadingStreakService::class)->summary($this->member, CarbonImmutable::parse('2026-07-18'));

        $this->assertSame(0, $summary['current']);
        $this->assertSame(2, $summary['longest']);
    }

    public function test_longest_streak_is_kept_after_a_break(): void
    {
        $this->recordDays(['2026-07-01', '2026-07-02', '2026-07-03', '2026-07-04', '2026-07-17', '2026-07-18']);

        $summary = app(ReadingStreakService::class)->summary($this->member, CarbonImmutable::parse('2026-07-18'));

        $this->assertSame(2, $summary['current']);
        $this->assertSame(4, $summary['longest']);
    }

    public function test_streak_is_zero_with_no_readings(): void
    {
        $summary = app(ReadingStreakService::class)->summary($this->member, CarbonImmutable::parse('2026-07-18'));

        $this->assertSame(0, $summary['current']);
        $this->assertSame(0, $summary['longest']);
        $this->assertNull($summary['last_completed_on']);
    }

    public function test_a_far_off_client_date_cannot_be_used_to_game_the_streak(): void
    {
        Sanctum::actingAs($this->user);
        $day = $this->dayFor('0718');

        // A date years away is ignored in favour of the server's day.
        $this->postJson("/api/me/reading/days/{$day->id}/complete", ['date' => '2030-01-01'])->assertOk();

        $this->assertTrue(
            MemberReadingProgress::query()
                ->where('member_id', $this->member->id)
                ->whereDate('completed_on', CarbonImmutable::now()->toDateString())
                ->exists(),
        );
    }

    /**
     * @param  list<string>  $dates
     */
    private function recordDays(array $dates): void
    {
        $day = $this->dayFor('0718');

        foreach ($dates as $index => $date) {
            MemberReadingProgress::create([
                'member_id' => $this->member->id,
                // A distinct day row per date keeps the unique constraint happy.
                'reading_day_id' => ReadingDay::factory()->create([
                    'reading_plan_id' => $this->plan->id,
                    'day_number' => 500 + $index,
                ])->id,
                'reading_plan_id' => $this->plan->id,
                'completed_on' => $date,
                'completed_at' => now(),
            ]);
        }

        unset($day);
    }
}
