<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendAccountSetupEmailJob;
use App\Mail\PublicEventRegistrationConfirmationMail;
use App\Mail\PublicEventRegistrationThankYouMail;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Requires MySQL: SQLite migrations fail on guest_follow_ups MODIFY. Example:
 * `DB_CONNECTION=mysql php artisan test tests/Feature/PublicEventRegistrationEmailTest.php`
 */
final class PublicEventRegistrationEmailTest extends TestCase
{
    use RefreshDatabase;

    private function makePublicEvent(): Event
    {
        $branch = Branch::factory()->create([
            'public_code' => 'yaba',
            'status' => 'active',
        ]);

        return Event::factory()->create([
            'branch_id' => $branch->id,
            'public_slug' => 'easter-potluck',
            'is_public' => true,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'name' => 'Easter Potluck',
        ]);
    }

    public function test_new_user_receives_confirmation_mail_with_attachment_and_account_setup_job(): void
    {
        Mail::fake();
        Queue::fake();

        $this->makePublicEvent();

        $email = 'new-registrant-'.uniqid('', true).'@example.com';

        $response = $this->postJson('/public-api/event/yaba/easter-potluck/register', [
            'name' => 'New Registrant',
            'email' => $email,
            'phone' => '08050602370',
        ]);

        $response->assertCreated();

        Mail::assertQueued(PublicEventRegistrationConfirmationMail::class, function (PublicEventRegistrationConfirmationMail $mail) use ($email): bool {
            return $mail->hasTo($email)
                && count($mail->attachments()) === 1;
        });

        Mail::assertNotQueued(PublicEventRegistrationThankYouMail::class);

        Queue::assertPushed(SendAccountSetupEmailJob::class, 1);
    }

    public function test_existing_user_receives_thank_you_only_and_no_account_setup_job(): void
    {
        Mail::fake();
        Queue::fake();

        $this->makePublicEvent();

        $email = 'existing-member-'.uniqid('', true).'@example.com';
        User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
            'name' => 'Existing Member',
        ]);

        $response = $this->postJson('/public-api/event/yaba/easter-potluck/register', [
            'name' => 'Existing Member',
            'email' => $email,
            'phone' => '08000000000',
        ]);

        $response->assertCreated();

        Mail::assertQueued(PublicEventRegistrationThankYouMail::class, function (PublicEventRegistrationThankYouMail $mail) use ($email): bool {
            return $mail->hasTo($email);
        });

        Mail::assertNotQueued(PublicEventRegistrationConfirmationMail::class);

        Queue::assertNotPushed(SendAccountSetupEmailJob::class);
    }
}
