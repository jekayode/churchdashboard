<?php

declare(strict_types=1);

namespace Tests\Feature\Builders;

use App\Enums\BuilderIndustry;
use App\Enums\BusinessChallenge;
use App\Enums\BusinessStage;
use App\Enums\CacStatus;
use App\Models\BuilderRegistration;
use App\Models\User;
use App\Notifications\BuilderAccountActivationNotification;
use App\Notifications\BuilderPackReadyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BuilderRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(string $email = 'builder@example.com'): array
    {
        return [
            'full_name' => 'Jane Builder',
            'phone' => '08012345678',
            'email' => $email,
            'business_name' => 'Acme Co',
            'business_description' => 'We sell handmade skincare to women aged 20-35.',
            'business_stage' => BusinessStage::IdeaNotStarted->value,
            'industry' => BuilderIndustry::FashionBeauty->value,
            'biggest_challenge' => BusinessChallenge::NoClearPlan->value,
            'success_vision' => '10 paying clients per month',
            'cac_status' => CacStatus::PlanningToRegister->value,
        ];
    }

    #[Test]
    public function registration_form_is_public(): void
    {
        $this->get(route('builders.create'))->assertOk();
    }

    #[Test]
    public function new_email_creates_user_and_sends_activation_notification(): void
    {
        Queue::fake();
        Notification::fake();

        $response = $this->post(route('builders.store'), $this->validPayload('newbuilder@example.com'));

        Queue::assertNothingPushed();

        $response->assertRedirect(route('builders.thank-you'));

        $this->assertDatabaseHas('users', [
            'email' => 'newbuilder@example.com',
            'email_verified_at' => null,
        ]);

        $this->assertDatabaseHas('builder_registrations', [
            'email' => 'newbuilder@example.com',
            'business_name' => 'Acme Co',
        ]);

        $user = User::query()->where('email', 'newbuilder@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, BuilderAccountActivationNotification::class);
    }

    #[Test]
    public function existing_email_updates_registration_and_sends_pack_ready_notification(): void
    {
        Queue::fake();
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('builders.store'), $this->validPayload('existing@example.com'));

        $response->assertRedirect(route('builders.thank-you'));

        Queue::assertNothingPushed();

        $this->assertEquals(1, User::query()->where('email', 'existing@example.com')->count());
        $this->assertDatabaseHas('builder_registrations', [
            'email' => 'existing@example.com',
            'user_id' => $user->id,
        ]);

        Notification::assertSentTo($user, BuilderPackReadyNotification::class);
        Notification::assertNotSentTo($user, BuilderAccountActivationNotification::class);
    }

    #[Test]
    public function registration_requires_valid_fields(): void
    {
        $response = $this->post(route('builders.store'), []);

        $response->assertSessionHasErrors(['full_name', 'email', 'phone', 'business_name']);
    }

    #[Test]
    public function verified_user_can_access_account_page_after_registration(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        BuilderRegistration::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $this->actingAs($user)
            ->get(route('builders.account'))
            ->assertOk();
    }

    #[Test]
    public function activation_sets_password_and_verifies_email(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'builders.activate.store',
            now()->addHour(),
            ['user' => $user->id]
        );

        $response = $this->post($url, [
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('builders.account'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Password123!', $user->password));
        $this->assertAuthenticatedAs($user);
    }
}
