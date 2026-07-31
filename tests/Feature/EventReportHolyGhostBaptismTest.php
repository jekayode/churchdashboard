<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchReportToken;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Holy Ghost baptism is the one attendance figure the Sunday service sheet
 * records that the report did not. It is optional — often zero — so it must
 * flow through when given and never block a submission when omitted.
 */
final class EventReportHolyGhostBaptismTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('super_admin');
        $this->branch = Branch::factory()->create(['status' => 'active']);
    }

    private function payload(Event $event, array $overrides = []): array
    {
        return array_merge([
            'event_id' => $event->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'Sunday Service',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'male_attendance' => 49,
            'female_attendance' => 38,
            'children_attendance' => 13,
            'online_attendance' => 0,
            'first_time_guests' => 3,
            'converts' => 2,
            'cars' => 7,
            'has_second_service' => false,
        ], $overrides);
    }

    #[Test]
    public function holy_ghost_baptism_is_saved_when_reported(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->admin)
            ->postJson('/api/reports/event-reports', $this->payload($event, ['holy_ghost_baptism' => 4]))
            ->assertSuccessful();

        $this->assertDatabaseHas('event_reports', [
            'event_id' => $event->id,
            'holy_ghost_baptism' => 4,
        ]);
    }

    #[Test]
    public function a_report_without_holy_ghost_baptism_still_submits(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        // The figure is often zero and must never be the reason a report bounces.
        $this->actingAs($this->admin)
            ->postJson('/api/reports/event-reports', $this->payload($event))
            ->assertSuccessful();

        $this->assertDatabaseHas('event_reports', ['event_id' => $event->id, 'converts' => 2]);
    }

    #[Test]
    public function the_second_service_records_its_own_holy_ghost_baptism(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->admin)
            ->postJson('/api/reports/event-reports', $this->payload($event, [
                'holy_ghost_baptism' => 2,
                'has_second_service' => true,
                'second_service_start_time' => '11:00',
                'second_service_end_time' => '12:30',
                'second_male_attendance' => 77,
                'second_female_attendance' => 63,
                'second_children_attendance' => 23,
                'second_first_time_guests' => 5,
                'second_converts' => 3,
                'second_cars' => 11,
                'second_holy_ghost_baptism' => 6,
            ]))
            ->assertSuccessful();

        $report = EventReport::firstWhere('event_id', $event->id);
        $this->assertSame(2, $report->holy_ghost_baptism);
        $this->assertSame(6, $report->second_service_holy_ghost_baptism);
        // The two services combine, like converts do.
        $this->assertSame(8, $report->combined_holy_ghost_baptism);
    }

    #[Test]
    public function the_public_submission_form_the_team_uses_renders_the_field(): void
    {
        Event::factory()->create(['branch_id' => $this->branch->id]);
        $token = BranchReportToken::createForBranch($this->branch->id, 'Service Team', 'team@example.test');

        // This is the link the Sunday team fills each week.
        $this->get(route('public.reports.submit', ['token' => $token->token]))
            ->assertOk()
            ->assertSee('Holy Ghost Baptism');
    }

    #[Test]
    public function the_pastor_and_admin_report_forms_render_the_field(): void
    {
        $pastor = User::factory()->create(['email_verified_at' => now()]);
        $pastor->assignRole('branch_pastor');
        $this->branch->update(['pastor_id' => $pastor->id]);
        $pastor->assignRole('branch_pastor', $this->branch->id);

        $this->actingAs($pastor->fresh())->get(route('pastor.reports'))
            ->assertOk()->assertSee('Holy Ghost Baptism');
        $this->actingAs($this->admin)->get(route('admin.reports'))
            ->assertOk()->assertSee('Holy Ghost Baptism');
    }
}
