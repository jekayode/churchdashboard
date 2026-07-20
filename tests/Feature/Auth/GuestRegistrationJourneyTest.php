<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use App\Services\GuestRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Proves the ground an app sign-up would stand on.
 *
 * The existing api/auth/register creates a User and nothing else, so an account
 * made through it reaches an app where every endpoint answers 404. The public
 * guest form does the job properly — this walks its whole journey to confirm
 * that, rather than taking the reading of it on trust.
 */
final class GuestRegistrationJourneyTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        Mail::fake();
        Notification::fake();

        $this->branch = Branch::factory()->create(['name' => 'LifePointe Greater Lekki', 'status' => 'active']);
    }

    /**
     * @return array<string, mixed>
     */
    private function guestPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Grace',
            'surname' => 'Okafor',
            'email' => 'grace@example.test',
            'phone' => '08012345678',
            'branch_id' => $this->branch->id,
            'consent_given' => true,
            'consent_given_at' => now(),
            'consent_ip' => '127.0.0.1',
        ], $overrides);
    }

    public function test_registering_creates_both_a_user_and_a_member(): void
    {
        $user = app(GuestRegistrationService::class)->registerGuest($this->guestPayload());

        $this->assertNotNull($user->id);

        $member = Member::where('user_id', $user->id)->first();
        $this->assertNotNull($member, 'Without a Member, every /me endpoint answers 404');
        $this->assertSame($this->branch->id, $member->branch_id);
        $this->assertSame('visitor', $member->member_status);
        $this->assertSame('guest-form', $member->registration_source);
    }

    public function test_the_consent_record_is_kept(): void
    {
        $user = app(GuestRegistrationService::class)->registerGuest($this->guestPayload());
        $member = Member::where('user_id', $user->id)->first();

        // A data-protection record, not a checkbox — an app sign-up has to
        // capture the same thing rather than quietly skipping it.
        $this->assertNotNull($member->consent_given_at);
        $this->assertSame('127.0.0.1', $member->consent_ip);
    }

    public function test_the_account_can_then_sign_in_and_use_the_app(): void
    {
        $user = app(GuestRegistrationService::class)->registerGuest($this->guestPayload());

        // The service generates a password nobody knows and emails a link to
        // set one. This stands in for the member following that link.
        $user->forceFill(['password' => Hash::make('chosen-password')])->save();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'grace@example.test',
            'password' => 'chosen-password',
            'device_name' => 'test-device',
        ])->assertOk();

        $token = $login->json('data.token');
        $this->assertNotEmpty($token);

        $headers = ['Authorization' => 'Bearer '.$token];

        // The point of the whole exercise: a real, usable app session.
        $this->getJson('/api/me', $headers)->assertOk();
        $this->getJson('/api/me/events', $headers)->assertOk();
        $this->getJson('/api/me/notes', $headers)->assertOk();
        $this->getJson('/api/me/quiz/history', $headers)->assertOk();
    }

    public function test_the_plain_register_endpoint_is_not_a_public_sign_up_path(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Stranger Person',
            'email' => 'stranger@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ]);

        /*
         * It demands a branch and a role, so it is an administrative
         * user-creation endpoint rather than somewhere a sign-up screen could
         * post to. Worth pinning: it is the obvious-looking route for app
         * sign-up and the wrong one, and it also creates no Member.
         */
        $response->assertStatus(422)->assertJsonValidationErrors(['branch_id', 'role_id']);
        $this->assertNull(User::firstWhere('email', 'stranger@example.test'));
    }

    public function test_a_duplicate_email_is_refused(): void
    {
        User::factory()->create(['email' => 'grace@example.test']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        app(GuestRegistrationService::class)->registerGuest($this->guestPayload());
    }
}
