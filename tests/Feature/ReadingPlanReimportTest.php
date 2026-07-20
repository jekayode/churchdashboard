<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberReadingProgress;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * These guard a command that gets run by hand on a live server, where the
 * natural response to a half-finished import is to run it again.
 */
final class ReadingPlanReimportTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'plan').'.csv';
        file_put_contents($path, $contents);

        return $path;
    }

    private function twoDays(string $firstReading = 'GENESIS 1:1-2:25'): string
    {
        return "month_day,label,old_testament,new_testament,psalm,proverbs,what_now_1\n"
            ."0101,January 1,{$firstReading},MATTHEW 1:1-2:12,PSALM 1:1-1:6,PROVERBS 1:1-1:6,First question\n"
            ."0102,January 2,GENESIS 3:1-4:26,MATTHEW 2:13-3:6,PSALM 2:1-2:12,PROVERBS 1:7-1:9,Second question\n";
    }

    public function test_a_second_import_is_refused_rather_than_quietly_duplicating(): void
    {
        $path = $this->csv($this->twoDays());

        $this->artisan('reading-plan:import', ['file' => $path, '--name' => 'Bible in a Year', '--annual' => true, '--default' => true])
            ->assertSuccessful();

        $this->artisan('reading-plan:import', ['file' => $path, '--name' => 'Bible in a Year', '--annual' => true, '--default' => true])
            ->expectsOutputToContain('already exists');

        // Otherwise the slug is quietly made unique, and --default hands the
        // flag to a plan nobody meant to publish.
        $this->assertSame(1, ReadingPlan::count());

        unlink($path);
    }

    public function test_replacing_updates_the_days_in_place(): void
    {
        $path = $this->csv($this->twoDays());
        $this->artisan('reading-plan:import', ['file' => $path, '--name' => 'Bible in a Year', '--annual' => true]);

        $corrected = $this->csv($this->twoDays('GENESIS 1:1-3:24'));
        $this->artisan('reading-plan:import', [
            'file' => $corrected, '--name' => 'Bible in a Year', '--annual' => true, '--replace' => true,
        ])->assertSuccessful();

        $this->assertSame(1, ReadingPlan::count());
        $plan = ReadingPlan::first();
        $this->assertSame(2, $plan->days()->count(), 'Replacing must not pile up extra days');
        $this->assertSame('GENESIS 1:1-3:24', $plan->days()->orderBy('day_number')->first()->old_testament);

        unlink($path);
        unlink($corrected);
    }

    public function test_replacing_keeps_every_members_reading_history(): void
    {
        $path = $this->csv($this->twoDays());
        $this->artisan('reading-plan:import', ['file' => $path, '--name' => 'Bible in a Year', '--annual' => true]);

        $member = Member::factory()->create();
        $plan = ReadingPlan::first();
        $day = $plan->days()->orderBy('day_number')->first();
        MemberReadingProgress::create([
            'member_id' => $member->id,
            'reading_plan_id' => $plan->id,
            'reading_day_id' => $day->id,
            'completed_on' => now()->toDateString(),
            'completed_at' => now(),
        ]);

        $corrected = $this->csv($this->twoDays('GENESIS 1:1-3:24'));
        $this->artisan('reading-plan:import', [
            'file' => $corrected, '--name' => 'Bible in a Year', '--annual' => true, '--replace' => true,
        ]);

        /*
         * member_reading_progress cascades on reading_day_id, so a
         * delete-then-recreate would take every member's history and streak
         * with it — silently, and only noticed when the streaks reset.
         */
        $this->assertSame(1, MemberReadingProgress::count(), 'Reading history must survive a re-import');
        $this->assertSame($day->id, $day->fresh()->id);

        unlink($path);
        unlink($corrected);
    }
}
