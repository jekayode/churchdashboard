<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ChurchPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Most members have never set a password — their account was created for them —
 * so without this, signing in to the app is a dead end.
 */
final class ApiForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_ask_for_a_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'grace@example.test']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'grace@example.test'])
            ->assertOk()
            ->assertJsonPath('success', true);

        // The app overrides Laravel's default with its own church-branded one.
        Notification::assertSentTo($user, ChurchPasswordResetNotification::class);
    }

    public function test_an_unknown_address_gets_the_same_answer(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.test']);

        /*
         * Telling a stranger whether an address is registered would turn this
         * into a way of finding out who attends the church. The reply is the
         * same either way.
         */
        $response->assertOk()->assertJsonPath('success', true);
        Notification::assertNothingSent();
    }

    public function test_the_reply_does_not_say_whether_the_account_exists(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'known@example.test']);

        $known = $this->postJson('/api/auth/forgot-password', ['email' => 'known@example.test']);
        $unknown = $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.test']);

        $this->assertSame($known->json(), $unknown->json(), 'The two replies must be indistinguishable');
    }

    public function test_a_malformed_address_is_rejected(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_requests_are_throttled(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'grace@example.test']);

        // Unthrottled, this is a way of posting mail to strangers from the
        // church's own domain.
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/forgot-password', ['email' => 'grace@example.test']);
        }

        $this->postJson('/api/auth/forgot-password', ['email' => 'grace@example.test'])
            ->assertStatus(429);
    }

    public function test_the_link_points_at_the_web_reset_page(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'grace@example.test']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'grace@example.test']);

        Notification::assertSentTo(
            $user,
            ChurchPasswordResetNotification::class,
            function (ChurchPasswordResetNotification $notification) use ($user): bool {
                $mail = $notification->toMail($user);

                /*
                 * The reset happens on the web page that already exists and is
                 * already tested, rather than reimplemented inside the app.
                 * The URL travels as view data, since this notification renders
                 * its own church-branded template rather than Laravel's.
                 */
                return str_contains($mail->viewData['actionUrl'] ?? '', 'reset-password');
            },
        );
    }
}
