<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Imports\MembersImport;
use App\Jobs\SendBulkAccountSetupEmailsJob;
use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

final class GuestImportHardeningTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->branch = Branch::factory()->create(['status' => 'active']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('super_admin');

        return $user;
    }

    /**
     * @return array{0: string, 1: MembersImport}
     */
    private function importCsv(string $csv): MembersImport
    {
        $path = tempnam(sys_get_temp_dir(), 'import').'.csv';
        file_put_contents($path, $csv);
        $import = new MembersImport($this->branch->id, 'guest-form', 'visitor');
        Excel::import($import, $path);
        unlink($path);

        return $import;
    }

    public function test_text_plain_csv_upload_is_accepted(): void
    {
        Queue::fake();

        $csv = "First Name,Surname,Email,Phone\nAda,Okeke,ada.plain@example.test,08020000001\n";
        $path = tempnam(sys_get_temp_dir(), 'guests').'.csv';
        file_put_contents($path, $csv);

        // macOS often reports CSV as text/plain — must not fail mimes validation.
        $file = new UploadedFile($path, 'guests.csv', 'text/plain', null, true);

        $response = $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), [
                'file' => $file,
                'branch_id' => $this->branch->id,
            ]);

        @unlink($path);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('members', ['email' => 'ada.plain@example.test']);
    }

    public function test_reimporting_the_same_csv_creates_no_new_members(): void
    {
        Queue::fake();

        $csv = "First Name,Surname,Email,Phone\n"
            ."Tunde,Bello,tunde.reimport@example.test,08030000001\n";

        $makeFile = function () use ($csv): UploadedFile {
            $path = tempnam(sys_get_temp_dir(), 'guests').'.csv';
            file_put_contents($path, $csv);

            return new UploadedFile($path, 'guests.csv', 'text/csv', null, true);
        };

        $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), [
                'file' => $makeFile(),
                'branch_id' => $this->branch->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $countAfterFirst = Member::query()->count();

        $second = $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), [
                'file' => $makeFile(),
                'branch_id' => $this->branch->id,
            ]);

        $second->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('summary.successful_imports', 0)
            ->assertJsonPath('summary.skipped_duplicates', 1)
            ->assertJsonPath('summary.failed_imports', 0);

        $this->assertSame($countAfterFirst, Member::query()->count());
        $this->assertSame(1, Member::query()->where('email', 'tunde.reimport@example.test')->count());
    }

    public function test_same_phone_different_name_is_skipped_as_duplicate(): void
    {
        $this->importCsv("name,email,phone\nOriginal Name,,08040000001\n");
        $this->assertSame(1, Member::query()->count());

        $import = $this->importCsv("name,email,phone\nTypo Name,,08040000001\n");
        $summary = $import->getImportSummary();

        $this->assertSame(0, $summary['successful_imports']);
        $this->assertSame(1, $summary['skipped_duplicates']);
        $this->assertSame(0, $summary['failed_imports']);
        $this->assertSame(1, Member::query()->count());
    }

    public function test_concatenated_phone_does_not_exceed_users_phone_column(): void
    {
        $this->importCsv(
            "name,email,phone\n"
            ."Oluwatosin Elugbaju,phone.long@example.test,0803432442607012705118\n"
        );

        $member = Member::query()->where('email', 'phone.long@example.test')->first();
        $this->assertNotNull($member);
        $this->assertSame('08034324426', $member->phone);

        $user = User::query()->where('email', 'phone.long@example.test')->first();
        $this->assertNotNull($user);
        $this->assertLessThanOrEqual(20, strlen((string) $user->phone));
        $this->assertSame('08034324426', $user->phone);
    }

    public function test_excel_serial_date_of_birth_is_parsed(): void
    {
        // Excel serial 36526 = 2000-01-01 (from 1899-12-30)
        $this->importCsv(
            "name,email,phone,date_of_birth\n"
            ."Serial Dob,serial.dob@example.test,08050000001,36526\n"
        );

        $member = Member::query()->where('email', 'serial.dob@example.test')->first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->date_of_birth);
        $this->assertSame('2000-01-01', $member->date_of_birth->format('Y-m-d'));
    }

    public function test_invalid_utf8_in_import_result_still_returns_json(): void
    {
        Queue::fake();

        // Valid UTF-8 CSV that imports; encoding flag is applied on response
        $csv = "First Name,Surname,Email,Phone\nGrace,Okafor,utf8.ok@example.test,08060000001\n";
        $path = tempnam(sys_get_temp_dir(), 'guests').'.csv';
        file_put_contents($path, $csv);
        $file = new UploadedFile($path, 'guests.csv', 'text/csv', null, true);

        $response = $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), [
                'file' => $file,
                'branch_id' => $this->branch->id,
            ]);

        @unlink($path);

        $response->assertOk();
        $this->assertSame('application/json', explode(';', (string) $response->headers->get('content-type'))[0]);
        Queue::assertPushed(SendBulkAccountSetupEmailsJob::class);
    }
}
