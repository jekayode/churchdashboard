<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeEventsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Member $member;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('church_member', $this->branch->id);
        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function event(array $attributes = []): Event
    {
        return Event::factory()->create(array_merge([
            'branch_id' => $this->branch->id,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'registration_type' => 'simple',
        ], $attributes));
    }

    public function test_guest_cannot_list_events(): void
    {
        $this->getJson('/api/me/events')->assertUnauthorized();
    }

    public function test_lists_only_upcoming_events_for_own_branch(): void
    {
        $mine = $this->event(['name' => 'My Branch Event']);
        $this->event(['name' => 'Past Event', 'start_date' => now()->subWeek()]);

        $otherBranch = Branch::factory()->create();
        Event::factory()->create([
            'branch_id' => $otherBranch->id,
            'status' => 'active',
            'start_date' => now()->addWeek(),
            'name' => 'Other Branch Event',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/events')->assertOk();

        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('My Branch Event', $names);
        $this->assertNotContains('Past Event', $names);
        $this->assertNotContains('Other Branch Event', $names);
        $this->assertSame($mine->id, $response->json('data.0.id'));
    }

    public function test_event_list_reports_registration_state(): void
    {
        $event = $this->event();
        Sanctum::actingAs($this->user);

        $this->getJson('/api/me/events')
            ->assertOk()
            ->assertJsonPath('data.0.is_registered', false);

        EventRegistration::create([
            'event_id' => $event->id,
            'member_id' => $this->member->id,
            'user_id' => $this->user->id,
            'name' => $this->member->name,
            'email' => $this->member->email,
            'registration_date' => now(),
        ]);

        $this->getJson('/api/me/events')
            ->assertOk()
            ->assertJsonPath('data.0.is_registered', true);
    }

    public function test_member_can_register_and_cancel(): void
    {
        $event = $this->event();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/events/{$event->id}/register")
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'member_id' => $this->member->id,
        ]);

        $this->deleteJson("/api/me/events/{$event->id}/register")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'member_id' => $this->member->id,
        ]);
    }

    public function test_cannot_register_twice(): void
    {
        $event = $this->event();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/events/{$event->id}/register")->assertCreated();
        $this->postJson("/api/me/events/{$event->id}/register")->assertStatus(409);
    }

    public function test_cannot_register_for_other_branch_event(): void
    {
        $otherBranch = Branch::factory()->create();
        $event = Event::factory()->create([
            'branch_id' => $otherBranch->id,
            'status' => 'active',
            'start_date' => now()->addWeek(),
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/events/{$event->id}/register")->assertForbidden();
    }

    public function test_cannot_register_when_registration_is_disabled(): void
    {
        $event = $this->event(['registration_type' => 'none']);
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/events/{$event->id}/register")->assertUnprocessable();
    }

    public function test_cannot_register_beyond_capacity(): void
    {
        $event = $this->event(['max_capacity' => 1]);

        EventRegistration::create([
            'event_id' => $event->id,
            'member_id' => Member::factory()->create(['branch_id' => $this->branch->id])->id,
            'name' => 'Someone Else',
            'email' => 'someone@example.com',
            'registration_date' => now(),
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/events/{$event->id}/register")
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_custom_form_fields_are_exposed_and_validated(): void
    {
        $event = $this->event([
            'registration_type' => 'form',
            'custom_form_fields' => [
                ['name' => 'gender', 'type' => 'select', 'label' => 'Gender', 'options' => ['Male', 'Female'], 'required' => true],
                ['name' => 'notes', 'type' => 'text', 'label' => 'Notes', 'required' => false],
            ],
        ]);

        Sanctum::actingAs($this->user);

        // The app receives the pastor's field definition so it can render natively.
        $this->getJson('/api/me/events')
            ->assertOk()
            ->assertJsonPath('data.0.registration_type', 'form')
            ->assertJsonPath('data.0.custom_form_fields.0.name', 'gender');

        // A value outside the pastor's options is rejected.
        $this->postJson("/api/me/events/{$event->id}/register", [
            'custom_fields' => ['gender' => 'Unknown'],
        ])->assertUnprocessable();

        $this->postJson("/api/me/events/{$event->id}/register", [
            'custom_fields' => ['gender' => 'Male', 'notes' => 'Bringing a friend'],
        ])->assertCreated();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'member_id' => $this->member->id,
        ]);

        $registration = EventRegistration::where('event_id', $event->id)->first();
        $this->assertSame('Male', $registration->custom_fields['gender']);
    }

    public function test_registered_endpoint_returns_only_my_registrations(): void
    {
        $mine = $this->event(['name' => 'Mine']);
        $theirs = $this->event(['name' => 'Theirs']);

        EventRegistration::create([
            'event_id' => $mine->id,
            'member_id' => $this->member->id,
            'user_id' => $this->user->id,
            'name' => $this->member->name,
            'email' => $this->member->email,
            'registration_date' => now(),
        ]);

        EventRegistration::create([
            'event_id' => $theirs->id,
            'member_id' => Member::factory()->create(['branch_id' => $this->branch->id])->id,
            'name' => 'Other Person',
            'email' => 'other@example.com',
            'registration_date' => now(),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/events/registered')->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Mine', $response->json('data.0.name'));
    }
}
