<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ApiSignUpTest extends TestCase
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
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Grace',
            'surname' => 'Okafor',
            'email' => 'grace@example.test',
            'phone' => '08012345678',
            'branch_id' => $this->branch->id,
            'password' => 'chosen-password',
            'consent_given' => true,
            'device_name' => 'test-device',
        ], $overrides);
    }

    public function test_signing_up_creates_a_usable_account_and_signs_them_in(): void
    {
        $response = $this->postJson('/api/auth/register/guest', $this->payload())->assertStatus(201);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // The whole point: a Member exists, so the app is not empty behind this.
        $user = User::firstWhere('email', 'grace@example.test');
        $this->assertNotNull(Member::where('user_id', $user->id)->first());

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$token])->assertOk();
    }

    public function test_they_can_sign_in_again_with_the_password_they_chose(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload());

        // The web form generates a password and emails it; someone signing up
        // in the app picked their own, and it has to be the one that works.
        $this->postJson('/api/auth/login', [
            'email' => 'grace@example.test',
            'password' => 'chosen-password',
            'device_name' => 'test-device',
        ])->assertOk();
    }

    public function test_new_sign_ups_land_as_visitors_for_a_pastor_to_promote(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload());

        $member = Member::firstWhere('email', 'grace@example.test');
        $this->assertSame('visitor', $member->member_status, 'Membership is a pastoral judgement, not a self-assignment');
    }

    public function test_consent_is_recorded_from_the_server_not_the_client(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload([
            // A client trying to dictate its own consent record.
            'consent_given_at' => '1999-01-01 00:00:00',
            'consent_ip' => '10.10.10.10',
        ]));

        $member = Member::firstWhere('email', 'grace@example.test');
        $this->assertNotNull($member->consent_given_at);
        $this->assertNotSame('1999', $member->consent_given_at->format('Y'));
        $this->assertNotSame('10.10.10.10', $member->consent_ip);
    }

    public function test_consent_is_required(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload(['consent_given' => false]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('consent_given');

        $this->assertNull(User::firstWhere('email', 'grace@example.test'));
    }

    public function test_every_field_the_church_needs_is_required(): void
    {
        $this->postJson('/api/auth/register/guest', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'surname', 'email', 'phone', 'branch_id', 'password']);
    }

    public function test_a_short_password_is_refused(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload(['password' => 'short']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_an_existing_email_is_pointed_at_signing_in(): void
    {
        User::factory()->create(['email' => 'grace@example.test']);

        $this->postJson('/api/auth/register/guest', $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_a_chosen_password_is_never_emailed_back(): void
    {
        $this->postJson('/api/auth/register/guest', $this->payload());

        /*
         * The welcome email carries the generated password and says to change
         * it. Sending that to someone who just chose their own would put their
         * password in their inbox in plain text.
         */
        Mail::assertNothingSent();
    }

    public function test_the_web_form_still_generates_and_emails_a_password(): void
    {
        // The app path must not have changed what happens for a guest filling
        // in the form on the website, who has no way to choose one.
        $user = app(\App\Services\GuestRegistrationService::class)->registerGuest([
            'first_name' => 'Tobi',
            'surname' => 'Adeyemi',
            'email' => 'tobi@example.test',
            'phone' => '08087654321',
            'branch_id' => $this->branch->id,
            'consent_given' => true,
            'consent_given_at' => now(),
            'consent_ip' => '127.0.0.1',
        ]);

        $this->assertNotNull($user);
        $this->assertFalse(Hash::check('chosen-password', $user->password));
    }

    // The branch list -----------------------------------------------------

    public function test_the_branch_list_is_available_before_signing_up(): void
    {
        Branch::factory()->create(['name' => 'LifePointe Yaba', 'status' => 'active']);

        $response = $this->getJson('/api/public/branches')->assertOk();

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_the_branch_list_hides_inactive_branches(): void
    {
        Branch::factory()->create(['name' => 'Closed Branch', 'status' => 'inactive']);

        $names = array_column($this->getJson('/api/public/branches')->json('data'), 'name');

        $this->assertNotContains('Closed Branch', $names);
    }

    public function test_the_branch_list_gives_away_nothing_but_id_and_name(): void
    {
        $response = $this->getJson('/api/public/branches')->assertOk();

        // A branch row also carries a venue, contact details and its pastor.
        // None of that belongs in an unauthenticated response.
        $this->assertSame(['id', 'name'], array_keys($response->json('data')[0]));
    }
}
