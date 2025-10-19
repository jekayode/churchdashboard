<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendBulkWelcomeEmailsJob;
use App\Models\Branch;
use App\Models\BranchReportToken;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class MemberImportWithWelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_import_creates_user_and_schedules_welcome_email(): void
    {
        Queue::fake();

        // Create a branch
        $branch = Branch::factory()->create();

        // Create a simple CSV content for import
        $csvContent = "name,email,phone,gender,member_status\n";
        $csvContent .= "John Doe,john.doe@example.com,+1234567890,male,member\n";
        $csvContent .= "Jane Smith,jane.smith@example.com,+1234567891,female,member\n";

        // Create a temporary file
        $file = tmpfile();
        fwrite($file, $csvContent);
        $path = stream_get_meta_data($file)['uri'];

        // Create upload file
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $path,
            'members.csv',
            'text/csv',
            null,
            true
        );

        // Import members
        $response = $this->postJson('/api/import-export/members/import', [
            'file' => $uploadedFile,
            'branch_id' => $branch->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Assert users were created
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.smith@example.com',
            'name' => 'Jane Smith',
        ]);

        // Assert members were created
        $this->assertDatabaseHas('members', [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe',
            'branch_id' => $branch->id,
            'member_status' => 'member',
        ]);

        $this->assertDatabaseHas('members', [
            'email' => 'jane.smith@example.com',
            'name' => 'Jane Smith',
            'branch_id' => $branch->id,
            'member_status' => 'member',
        ]);

        // Assert welcome email job was dispatched
        Queue::assertPushed(SendBulkWelcomeEmailsJob::class, function ($job) use ($branch) {
            return $job->branch->id === $branch->id && count($job->userData) === 2;
        });

        // Clean up
        fclose($file);
    }

    public function test_branch_report_token_creation_and_usage(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();

        // Create an event
        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'is_published' => true,
            'status' => 'active',
        ]);

        // Create a report token
        $token = BranchReportToken::createForBranch(
            $branch->id,
            'Service Chief - Main Branch',
            'chief@example.com',
            [$event->id]
        );

        $this->assertDatabaseHas('branch_report_tokens', [
            'branch_id' => $branch->id,
            'name' => 'Service Chief - Main Branch',
            'email' => 'chief@example.com',
            'is_active' => true,
        ]);

        // Test token validity
        $this->assertTrue($token->isValid());

        // Test public submission form access
        $response = $this->get("/public/reports/submit/{$token->token}");
        $response->assertStatus(200);
        $response->assertSee('Event Report Submission');
        $response->assertSee($branch->name);

        // Test token usage recording
        $initialUsageCount = $token->usage_count;
        $token->recordUsage();
        $token->refresh();

        $this->assertEquals($initialUsageCount + 1, $token->usage_count);
        $this->assertNotNull($token->last_used_at);
    }

    public function test_public_report_submission(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();

        // Create an event
        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'is_published' => true,
            'status' => 'active',
        ]);

        // Create a report token
        $token = BranchReportToken::createForBranch(
            $branch->id,
            'Service Chief - Main Branch'
        );

        // Submit a report via public link
        $reportData = [
            'event_id' => $event->id,
            'event_date' => now()->format('Y-m-d'),
            'event_type' => 'Sunday Service',
            'service_type' => 'Morning Service',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'male_attendance' => 25,
            'female_attendance' => 30,
            'children_attendance' => 15,
            'online_attendance' => 10,
            'first_time_guests' => 3,
            'converts' => 1,
            'cars' => 20,
            'notes' => 'Great service with powerful worship',
        ];

        $response = $this->postJson("/public/reports/submit/{$token->token}", $reportData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Report submitted successfully! Thank you for your submission.',
        ]);

        // Assert report was created
        $this->assertDatabaseHas('event_reports', [
            'event_id' => $event->id,
            'attendance_male' => 25,
            'attendance_female' => 30,
            'attendance_children' => 15,
            'attendance_online' => 10,
            'first_time_guests' => 3,
            'converts' => 1,
            'number_of_cars' => 20,
        ]);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get('/public/reports/submit/invalid-token');
        $response->assertStatus(404);
    }

    public function test_expired_token_returns_404(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();

        // Create an expired token
        $token = BranchReportToken::createForBranch(
            $branch->id,
            'Expired Token',
            null,
            null,
            now()->subDay() // Expired yesterday
        );

        $response = $this->get("/public/reports/submit/{$token->token}");
        $response->assertStatus(404);
    }
}
