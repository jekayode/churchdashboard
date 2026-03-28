<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PastorEventFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_super_admin_can_view_create_and_edit_event_forms(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super_admin');

        $branch = Branch::factory()->create(['status' => 'active']);
        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'start_date' => now()->addWeek(),
        ]);

        $this->actingAs($admin);

        $this->get(route('pastor.events.create'))
            ->assertOk()
            ->assertSee('Create event', false);

        $this->get(route('pastor.events.edit', $event))
            ->assertOk()
            ->assertSee('Edit event', false);
    }

    public function test_branch_pastor_can_view_edit_form_for_own_branch_event(): void
    {
        $pastor = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $pastor->assignRole('branch_pastor');

        $branch = Branch::factory()->create([
            'pastor_id' => $pastor->id,
            'status' => 'active',
        ]);
        $pastor->assignRole('branch_pastor', $branch->id);

        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'start_date' => now()->addWeek(),
        ]);

        $this->actingAs($pastor);

        $this->get(route('pastor.events.edit', $event))
            ->assertOk();
    }

    public function test_guest_is_redirected_from_event_form_routes(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $event = Event::factory()->create([
            'branch_id' => $branch->id,
            'start_date' => now()->addWeek(),
        ]);

        $this->get(route('pastor.events.create'))
            ->assertRedirect();

        $this->get(route('pastor.events.edit', $event))
            ->assertRedirect();
    }
}
