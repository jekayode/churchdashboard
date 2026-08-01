<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Imports\MembersImport;
use App\Models\Branch;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * The members table has first_name and surname columns, and the importer
 * accepted those headers — then dropped them, keeping only a combined name. So
 * an import left every member with a blank first_name and surname, which the
 * app increasingly relies on (the leaderboard, the greeting, the profile).
 */
final class GuestImportNameTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        Mail::fake();
        $this->branch = Branch::factory()->create();
    }

    private function import(string $csv): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.csv';
        file_put_contents($path, $csv);
        Excel::import(new MembersImport($this->branch->id, 'guest-form', 'visitor'), $path);
        unlink($path);
    }

    public function test_separate_first_name_and_surname_columns_are_saved(): void
    {
        $this->import("first_name,surname,email,phone\nGrace,Okafor,grace@example.test,08010000001\n");

        $member = Member::firstWhere('email', 'grace@example.test');
        $this->assertNotNull($member);
        $this->assertSame('Grace', $member->first_name);
        $this->assertSame('Okafor', $member->surname);
        // The combined name is still filled, because much of the app reads it.
        $this->assertSame('Grace Okafor', $member->name);
    }

    public function test_a_single_combined_name_is_split_across_the_columns(): void
    {
        // Old files, and the odd hand-made one, still carry just "name".
        $this->import("name,email,phone\nGrace Okafor,grace@example.test,08010000002\n");

        $member = Member::firstWhere('email', 'grace@example.test');
        $this->assertSame('Grace', $member->first_name);
        $this->assertSame('Okafor', $member->surname);
        $this->assertSame('Grace Okafor', $member->name);
    }

    public function test_a_multi_word_surname_stays_whole(): void
    {
        $this->import("name,email,phone\nMary Jane Van Der Berg,mary@example.test,08010000003\n");

        $member = Member::firstWhere('email', 'mary@example.test');
        $this->assertSame('Mary', $member->first_name);
        $this->assertSame('Jane Van Der Berg', $member->surname, 'Only the first space splits the name');
    }

    public function test_a_profession_column_imports_to_occupation(): void
    {
        // The church calls it Profession; the column is occupation, and the
        // importer already maps the header — this guards that it stays wired.
        $this->import('first_name,surname,email,phone,profession
Grace,Okafor,grace@example.test,08010000004,Nurse
');

        $member = Member::firstWhere('email', 'grace@example.test');
        $this->assertSame('Nurse', $member->occupation);
    }

    public function test_the_template_advertises_the_profession_column(): void
    {
        $result = app(\App\Services\GuestManagementService::class)->getGuestImportTemplate();
        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($result['file_path']);
        Excel::import(new MembersImport($this->branch->id, 'guest-form', 'visitor'), $path);

        // Round-trip proves the Profession heading and its column values line up:
        // the sample row's profession lands in occupation.
        $john = Member::firstWhere('name', 'John Doe');
        $this->assertNotNull($john->occupation, 'Template Profession column did not map through');
    }

    public function test_the_downloaded_template_re_imports_with_its_names_split(): void
    {
        // The strongest guarantee is that the loop closes: generate the template
        // a pastor downloads, feed it straight back to the importer, and confirm
        // the sample rows land with first_name and surname filled. This catches
        // the header row and the data rows drifting apart, which is exactly what
        // a hand-checked assertion on either one alone would miss.
        $result = app(\App\Services\GuestManagementService::class)->getGuestImportTemplate();
        $this->assertTrue($result['success']);

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($result['file_path']);
        Excel::import(new MembersImport($this->branch->id, 'guest-form', 'visitor'), $path);

        $john = Member::firstWhere('name', 'John Doe');
        $this->assertNotNull($john, 'The template sample row did not import');
        $this->assertSame('John', $john->first_name);
        $this->assertSame('Doe', $john->surname);
    }
}
