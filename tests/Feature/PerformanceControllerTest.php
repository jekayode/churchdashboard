<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PerformanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $branchPastor;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles first
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Create test users with roles
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->branchPastor = User::factory()->create();
        $this->branchPastor->assignRole('branch_pastor');

        // Create test branch
        $this->branch = Branch::factory()->create([
            'pastor_id' => $this->branchPastor->id,
        ]);

        // Assign branch pastor role with branch context
        $this->branchPastor->assignRole('branch_pastor', $this->branch->id);
    }

    /** @test */
    public function can_get_branch_performance_data(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        // Create event reports for the branch
        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'second_service_first_time_guests' => 5,
            'converts' => 3,
            'second_service_converts' => 2,
        ]);

        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-02-15',
            'attendance_male' => 45,
            'attendance_female' => 55,
            'attendance_children' => 25,
            'attendance_online' => 15,
            'first_time_guests' => 8,
            'second_service_first_time_guests' => 3,
            'converts' => 2,
            'second_service_converts' => 1,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/performance/branch?branch_id={$this->branch->id}&year=2024");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'branch_id',
                    'year',
                    'actuals' => [
                        'attendance',
                        'guests',
                        'converts',
                        'weekly_avg_attendance',
                    ],
                    'quarters' => [
                        '*' => [
                            'quarter',
                            'current' => [
                                'attendance',
                                'guests',
                                'converts',
                            ],
                            'previous' => [
                                'attendance',
                                'guests',
                                'converts',
                            ],
                            'delta' => [
                                'attendance',
                                'guests',
                                'converts',
                            ],
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals($this->branch->id, $data['branch_id']);
        $this->assertEquals(2024, $data['year']);
        $this->assertEquals(300, $data['actuals']['attendance']); // 160 + 140
        $this->assertEquals(26, $data['actuals']['guests']); // 15 + 11
        $this->assertEquals(8, $data['actuals']['converts']); // 5 + 3
    }

    /** @test */
    public function can_get_branch_performance_with_custom_date_range(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        // Create event reports
        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-03-15', // Outside date range
            'attendance_male' => 100,
            'attendance_female' => 100,
            'attendance_children' => 50,
            'attendance_online' => 25,
            'first_time_guests' => 20,
            'converts' => 10,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/performance/branch?branch_id={$this->branch->id}&year=2024&start_date=2024-01-01&end_date=2024-02-28");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(160, $data['actuals']['attendance']); // Only first report
        $this->assertEquals(10, $data['actuals']['guests']); // Only first report
        $this->assertEquals(3, $data['actuals']['converts']); // Only first report
    }

    /** @test */
    public function branch_performance_returns_zero_when_no_data(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/performance/branch?branch_id={$this->branch->id}&year=2024");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(0, $data['actuals']['attendance']);
        $this->assertEquals(0, $data['actuals']['guests']);
        $this->assertEquals(0, $data['actuals']['converts']);
    }

    /** @test */
    public function branch_performance_uses_pastor_branch_when_no_branch_id_provided(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->getJson('/api/performance/branch?year=2024');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals($this->branch->id, $data['branch_id']);
        $this->assertEquals(160, $data['actuals']['attendance']);
    }

    /** @test */
    public function branch_performance_returns_error_when_no_branch_specified(): void
    {
        $userWithoutBranch = User::factory()->create();
        $userWithoutBranch->assignRole('church_member');

        $response = $this->actingAs($userWithoutBranch)
            ->getJson('/api/performance/branch?year=2024');

        $response->assertStatus(403);
    }

    /** @test */
    public function can_get_network_performance_data(): void
    {
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();

        $event1 = Event::factory()->create(['branch_id' => $branch1->id]);
        $event2 = Event::factory()->create(['branch_id' => $branch2->id]);

        // Create event reports for both branches
        EventReport::factory()->create([
            'event_id' => $event1->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        EventReport::factory()->create([
            'event_id' => $event2->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 40,
            'attendance_female' => 50,
            'attendance_children' => 20,
            'attendance_online' => 15,
            'first_time_guests' => 8,
            'converts' => 2,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/performance/network?year=2024');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'year',
                    'actuals' => [
                        'attendance',
                        'guests',
                        'converts',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(2024, $data['year']);
        $this->assertEquals(285, $data['actuals']['attendance']); // 160 + 125
        $this->assertEquals(18, $data['actuals']['guests']); // 10 + 8
        $this->assertEquals(5, $data['actuals']['converts']); // 3 + 2
    }

    /** @test */
    public function can_get_network_performance_with_custom_date_range(): void
    {
        $branch1 = Branch::factory()->create();
        $event1 = Event::factory()->create(['branch_id' => $branch1->id]);

        // Create event reports
        EventReport::factory()->create([
            'event_id' => $event1->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        EventReport::factory()->create([
            'event_id' => $event1->id,
            'event_type' => 'service',
            'report_date' => '2024-03-15', // Outside date range
            'attendance_male' => 100,
            'attendance_female' => 100,
            'attendance_children' => 50,
            'attendance_online' => 25,
            'first_time_guests' => 20,
            'converts' => 10,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/performance/network?year=2024&start_date=2024-01-01&end_date=2024-02-28');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(160, $data['actuals']['attendance']); // Only first report
        $this->assertEquals(10, $data['actuals']['guests']); // Only first report
        $this->assertEquals(3, $data['actuals']['converts']); // Only first report
    }

    /** @test */
    public function network_performance_returns_zero_when_no_data(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/performance/network?year=2024');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(0, $data['actuals']['attendance']);
        $this->assertEquals(0, $data['actuals']['guests']);
        $this->assertEquals(0, $data['actuals']['converts']);
    }

    /** @test */
    public function network_performance_defaults_to_current_year(): void
    {
        $branch = Branch::factory()->create();
        $event = Event::factory()->create(['branch_id' => $branch->id]);

        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2025-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/performance/network');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(now()->year, $data['year']);
        $this->assertEquals(160, $data['actuals']['attendance']);
    }

    /** @test */
    public function branch_performance_defaults_to_current_year(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2025-01-15',
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/performance/branch?branch_id={$this->branch->id}");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(now()->year, $data['year']);
        $this->assertEquals(160, $data['actuals']['attendance']);
    }

    /** @test */
    public function performance_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/performance/branch');
        $response->assertUnauthorized();

        $response = $this->getJson('/api/performance/network');
        $response->assertUnauthorized();
    }

    /** @test */
    public function quarter_comparison_includes_all_four_quarters(): void
    {
        $event = Event::factory()->create(['branch_id' => $this->branch->id]);

        // Create event reports for current year Q1
        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2024-01-15', // Q1
            'attendance_male' => 50,
            'attendance_female' => 60,
            'attendance_children' => 30,
            'attendance_online' => 20,
            'first_time_guests' => 10,
            'converts' => 3,
        ]);

        // Create event reports for previous year Q1
        EventReport::factory()->create([
            'event_id' => $event->id,
            'event_type' => 'service',
            'report_date' => '2023-01-15', // Q1 previous year
            'attendance_male' => 40,
            'attendance_female' => 50,
            'attendance_children' => 20,
            'attendance_online' => 15,
            'first_time_guests' => 8,
            'converts' => 2,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/performance/branch?branch_id={$this->branch->id}&year=2024");

        $response->assertOk();

        $data = $response->json('data');
        $quarters = $data['quarters'];

        $this->assertCount(4, $quarters);
        $this->assertEquals('Q1', $quarters[0]['quarter']);
        $this->assertEquals('Q2', $quarters[1]['quarter']);
        $this->assertEquals('Q3', $quarters[2]['quarter']);
        $this->assertEquals('Q4', $quarters[3]['quarter']);

        // Check Q1 data
        $q1 = $quarters[0];
        $this->assertEquals(160, $q1['current']['attendance']);
        $this->assertEquals(125, $q1['previous']['attendance']);
        $this->assertEquals(28.0, $q1['delta']['attendance']); // (160-125)/125 * 100
    }
}
