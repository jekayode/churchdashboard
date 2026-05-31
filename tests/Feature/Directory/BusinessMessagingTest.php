<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Models\Business;
use App\Models\BusinessMessage;
use App\Models\User;
use App\Notifications\BusinessMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class BusinessMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_message_business_and_owner_can_reply(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'Inquiry',
            'body' => 'Hello, are you available Saturday?',
        ]);

        $response->assertCreated();
        $threadId = $response->json('data.thread_id');

        Notification::assertSentTo($owner, BusinessMessageReceived::class);

        $this->actingAs($owner, 'sanctum');

        $this->postJson("/api/biz/messages/threads/{$threadId}/reply", [
            'body' => 'Yes, we are open!',
        ])->assertCreated();

        $this->assertEquals(2, BusinessMessage::where('thread_id', $threadId)->count());
    }

    public function test_owner_cannot_message_own_business(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();
        $this->actingAs($owner, 'sanctum');

        $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'Test',
            'body' => 'Self message',
        ])->assertForbidden();
    }

    public function test_repeat_messages_from_same_customer_reuse_thread(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($customer, 'sanctum');

        $first = $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'First inquiry',
            'body' => 'Hi',
        ])->json('data.thread_id');

        $second = $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'Second inquiry',
            'body' => 'Following up',
        ])->json('data.thread_id');

        $this->assertSame($first, $second);
        $this->assertEquals(1, BusinessMessage::query()->distinct('thread_id')->count('thread_id'));
    }

    public function test_customer_can_reply_to_their_own_thread(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($customer, 'sanctum');

        $threadId = $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'Hello',
            'body' => 'First message',
        ])->json('data.thread_id');

        $this->postJson("/api/biz/messages/threads/{$threadId}/reply", [
            'body' => 'Follow-up from customer',
        ])->assertCreated();

        $this->assertEquals(2, BusinessMessage::where('thread_id', $threadId)->count());
    }

    public function test_unrelated_user_cannot_reply_to_thread(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $stranger = User::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($customer, 'sanctum');
        $threadId = $this->postJson("/api/biz/businesses/{$business->slug}/messages", [
            'subject' => 'Hello',
            'body' => 'First message',
        ])->json('data.thread_id');

        $this->actingAs($stranger, 'sanctum');
        $this->postJson("/api/biz/messages/threads/{$threadId}/reply", [
            'body' => 'I should not be here',
        ])->assertForbidden();
    }
}
