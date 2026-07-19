<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReadingPlanImportTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        // Mirrors the church's real One Year Bible export, including the
        // cross-chapter and cross-book reference shapes it contains.
        $this->path = sys_get_temp_dir().'/reading_plan_test_'.uniqid().'.csv';

        $csv = "date,month_day,label,old_testament,new_testament,psalm,proverbs,what_now_1,what_now_2,source_url\n";
        $csv .= '2025-01-01,0101,January 1,GENESIS 1:1-2:25,MATTHEW 1:1-2:12,PSALM 1:1-1:6,PROVERBS 1:1-1:6,"First question, with a comma.",Second question,https://example.test/0101'."\n";
        $csv .= '2025-07-17,0717,July 17,1 CHRONICLES 24:1-26:11,ROMANS 4:1-12,PSALM 13:1-6,PROVERBS 19:15-16,Question one,,https://example.test/0717'."\n";
        $csv .= '2025-02-28,0228,February 28,LEVITICUS 27:14-NUMBERS 1:1-54,MARK 1:1-28,PSALM 35:1-16,PROVERBS 9:11-12,Question one,Question two,https://example.test/0228'."\n";

        file_put_contents($this->path, $csv);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('reading-plan:import', ['file' => $this->path, '--annual' => true, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('reading_plans', 0);
        $this->assertDatabaseCount('reading_days', 0);
    }

    public function test_imports_days_with_all_four_reading_groups(): void
    {
        $this->artisan('reading-plan:import', [
            'file' => $this->path,
            '--name' => 'Bible in a Year',
            '--annual' => true,
            '--publish' => true,
        ])->assertSuccessful();

        $plan = ReadingPlan::firstWhere('name', 'Bible in a Year');

        $this->assertNotNull($plan);
        $this->assertTrue($plan->is_annual);
        $this->assertTrue($plan->is_published);
        $this->assertSame(3, $plan->length_days);
        $this->assertSame(3, $plan->days()->count());

        $day = $plan->days()->where('month_day', '0717')->first();

        $this->assertSame('July 17', $day->label);
        $this->assertSame('1 CHRONICLES 24:1-26:11', $day->old_testament);
        $this->assertSame('ROMANS 4:1-12', $day->new_testament);
        $this->assertSame('PSALM 13:1-6', $day->psalm);
        $this->assertSame('PROVERBS 19:15-16', $day->proverbs);
        $this->assertCount(4, $day->references());
    }

    public function test_study_questions_are_kept_and_blank_ones_dropped(): void
    {
        $this->artisan('reading-plan:import', ['file' => $this->path, '--annual' => true])->assertSuccessful();

        $plan = ReadingPlan::first();

        $twoQuestions = $plan->days()->where('month_day', '0101')->first();
        $this->assertCount(2, $twoQuestions->studyQuestions());
        $this->assertStringContainsString('First question, with a comma.', $twoQuestions->study_question_1);

        // The real export leaves the second question blank on some days.
        $oneQuestion = $plan->days()->where('month_day', '0717')->first();
        $this->assertCount(1, $oneQuestion->studyQuestions());
    }

    public function test_annual_plan_resolves_the_day_for_a_date(): void
    {
        $this->artisan('reading-plan:import', ['file' => $this->path, '--annual' => true])->assertSuccessful();

        $plan = ReadingPlan::first();

        $this->assertSame('July 17', $plan->dayForDate(new \DateTime('2026-07-17'))->label);
        // Same content serves any year, since annual plans key on month-day.
        $this->assertSame('July 17', $plan->dayForDate(new \DateTime('2031-07-17'))->label);
    }

    public function test_leap_day_falls_back_to_february_28(): void
    {
        $this->artisan('reading-plan:import', ['file' => $this->path, '--annual' => true])->assertSuccessful();

        $plan = ReadingPlan::first();

        // A 365-day plan has no 29 February, so the reader must not lose a day.
        $this->assertSame('February 28', $plan->dayForDate(new \DateTime('2028-02-29'))->label);
    }

    public function test_marking_a_plan_default_clears_the_previous_default(): void
    {
        $existing = ReadingPlan::create([
            'name' => 'Old Default', 'type' => 'passages', 'is_default' => true, 'is_published' => true,
        ]);

        $this->artisan('reading-plan:import', [
            'file' => $this->path, '--name' => 'New Default', '--annual' => true, '--default' => true,
        ])->assertSuccessful();

        $this->assertFalse($existing->refresh()->is_default);
        $this->assertTrue(ReadingPlan::firstWhere('name', 'New Default')->is_default);
    }

    public function test_missing_file_fails_cleanly(): void
    {
        $this->artisan('reading-plan:import', ['file' => '/no/such/file.csv'])
            ->assertFailed();
    }
}
