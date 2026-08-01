<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendBulkAccountSetupEmailsJob;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * A successful import must return its summary immediately as JSON. Regression
 * guard for the hang that showed in the browser as "Server returned an invalid
 * response": the account-setup email job paces itself with sleep() between
 * batches, so when the queue connection is "sync" it ran inline and blocked the
 * request past PHP's max execution time, and the browser got a truncated HTML
 * page. The job must be enqueued, never run inline.
 */
final class GuestImportResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_a_successful_import_returns_json_and_does_not_run_the_email_job_inline(): void
    {
        // Faked so the pacing job can never execute inline and stall the request
        // — which is exactly the failure this guards against.
        Queue::fake();

        $branch = Branch::factory()->create(['status' => 'active']);
        $csv = "First Name,Surname,Email,Phone,Gender,Marital Status\n"
            ."Grace,Okafor,grace.import@example.test,08010000001,Female,Single\n";
        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $response = $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), ['file' => $file, 'branch_id' => $branch->id]);

        $response->assertOk()->assertJson(['success' => true]);

        // Enqueued for a worker, not executed during the request.
        Queue::assertPushed(SendBulkAccountSetupEmailsJob::class);
        $this->assertDatabaseHas('members', ['email' => 'grace.import@example.test']);
    }

    public function test_a_bad_upload_returns_a_json_error_not_an_html_page(): void
    {
        // The frontend now sends Accept: application/json, so validation failures
        // must come back as JSON it can display, never an HTML redirect.
        $response = $this->actingAs($this->superAdmin())
            ->postJson(route('guests.import'), ['branch_id' => 999999]);

        $response->assertStatus(422);
        $this->assertSame('application/json', explode(';', (string) $response->headers->get('content-type'))[0]);
    }
}
