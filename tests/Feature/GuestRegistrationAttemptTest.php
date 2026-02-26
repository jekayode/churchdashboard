<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRegistrationAttemptTest extends TestCase
{
    use RefreshDatabase;

    private function validGuestPayload(int $branchId, array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'surname' => 'Doe',
            'email' => 'jane.doe.'.uniqid().'@example.com',
            'phone' => '+2348012345678',
            'branch_id' => (string) $branchId,
            'consent_given' => '1',
        ], $overrides);
    }

    public function test_successful_registration_creates_attempt_with_status_success(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $payload = $this->validGuestPayload($branch->id);

        $response = $this->post(route('public.guest-register.store'), $payload);

        $response->assertRedirect(route('member.profile-completion'));
        $this->assertDatabaseHas('guest_registration_attempts', [
            'email' => $payload['email'],
            'status' => 'success',
        ]);
    }

    public function test_validation_failure_logs_attempt_with_status_validation_failed(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $payload = $this->validGuestPayload($branch->id);
        $payload['email'] = ''; // invalid

        $response = $this->post(route('public.guest-register.store'), $payload);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseHas('guest_registration_attempts', [
            'first_name' => 'Jane',
            'surname' => 'Doe',
            'status' => 'validation_failed',
            'error_type' => 'validation',
        ]);
    }

    public function test_duplicate_email_logs_attempt_with_status_database_error(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $payload = $this->validGuestPayload($branch->id, ['email' => 'existing@example.com']);

        $response = $this->post(route('public.guest-register.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('guest_registration_attempts', [
            'email' => 'existing@example.com',
            'status' => 'database_error',
        ]);
    }

    public function test_attempts_page_requires_auth_and_guest_permission(): void
    {
        $response = $this->get(route('guests.attempts'));
        $response->assertRedirect();
    }
}
