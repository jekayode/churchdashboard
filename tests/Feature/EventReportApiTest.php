<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventReportApiTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $branchPastor;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->branchPastor = User::factory()->create();
        $this->branchPastor->assignRole('branch_pastor');

        $this->branch = Branch::factory()->create([
            'pastor_id' => $this->branchPastor->id,
        ]);

        $this->branchPastor->assignRole('branch_pastor', $this->branch->id);
    }

    #[Test]
    public function show_event_report_includes_attendance_online_in_json(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        $report = EventReport::factory()->create([
            'event_id' => $event->id,
            'reported_by' => $this->superAdmin->id,
            'attendance_online' => 42,
            'event_type' => 'Sunday Service',
            'report_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/reports/event-reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.attendance_online', 42);
    }

    #[Test]
    public function update_event_report_persists_online_attendance(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        $report = EventReport::factory()->create([
            'event_id' => $event->id,
            'reported_by' => $this->superAdmin->id,
            'attendance_online' => 5,
            'attendance_male' => 10,
            'attendance_female' => 10,
            'attendance_children' => 5,
            'first_time_guests' => 1,
            'converts' => 0,
            'number_of_cars' => 1,
            'event_type' => 'Sunday Service',
            'report_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
        ]);

        $payload = [
            'event_id' => $event->id,
            'event_date' => $report->report_date->format('Y-m-d'),
            'event_type' => 'Sunday Service',
            'service_type' => $report->service_type,
            'start_time' => '09:00',
            'end_time' => '10:30',
            'notes' => null,
            'male_attendance' => 10,
            'female_attendance' => 10,
            'children_attendance' => 5,
            'online_attendance' => 99,
            'first_time_guests' => 1,
            'converts' => 0,
            'cars' => 1,
            'has_second_service' => false,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/reports/event-reports/{$report->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.attendance_online', 99);

        $this->assertDatabaseHas('event_reports', [
            'id' => $report->id,
            'attendance_online' => 99,
        ]);
    }

    #[Test]
    public function events_index_orders_by_created_at_desc_when_requested(): void
    {
        $older = Event::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Older Event',
        ]);
        $older->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();

        $newer = Event::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Newer Event',
        ]);
        $newer->forceFill(['created_at' => now()->subDay()])->saveQuietly();

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/events?branch_id='.$this->branch->id.'&sort_by=created_at&sort_order=desc&per_page=50');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $posNewer = array_search($newer->id, $ids, true);
        $posOlder = array_search($older->id, $ids, true);
        $this->assertNotFalse($posNewer);
        $this->assertNotFalse($posOlder);
        $this->assertLessThan($posOlder, $posNewer);
    }
}
