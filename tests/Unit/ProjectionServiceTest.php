<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Services\ProjectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class ProjectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProjectionService(Cache::store());
    }

    public function test_default_quarter_weights(): void
    {
        $weights = ProjectionService::defaultQuarterWeights();

        $this->assertCount(4, $weights);
        $this->assertEquals([15, 20, 30, 35], $weights);
        $this->assertEquals(100, array_sum($weights));
    }

    public function test_distribute_to_quarters_with_valid_weights(): void
    {
        $total = 1000;
        $weights = [15, 20, 30, 35];

        $result = $this->service->distributeToQuarters($total, $weights);

        $this->assertCount(4, $result);
        $this->assertEquals($total, array_sum($result));
        $this->assertEquals([150, 200, 300, 350], $result);
    }

    public function test_distribute_to_quarters_with_invalid_weights(): void
    {
        $total = 1000;
        $invalidWeights = [10, 20, 30]; // Wrong count

        $result = $this->service->distributeToQuarters($total, $invalidWeights);

        // Should fall back to default weights
        $this->assertCount(4, $result);
        $this->assertEquals($total, array_sum($result));
    }

    public function test_distribute_to_quarters_with_weights_not_summing_to100(): void
    {
        $total = 1000;
        $invalidWeights = [10, 20, 30, 25]; // Sums to 85, not 100

        $result = $this->service->distributeToQuarters($total, $invalidWeights);

        // Should fall back to default weights
        $this->assertCount(4, $result);
        $this->assertEquals($total, array_sum($result));
    }

    public function test_distribute_to_quarters_with_remainder(): void
    {
        $total = 1001; // Will have remainder
        $weights = [15, 20, 30, 35];

        $result = $this->service->distributeToQuarters($total, $weights);

        $this->assertCount(4, $result);
        $this->assertEquals($total, array_sum($result));

        // Check that largest remainder gets the extra unit
        $expected = [150, 200, 300, 351]; // Q4 gets the extra 1
        $this->assertEquals($expected, $result);
    }

    public function test_distribute_to_quarters_with_zero_total(): void
    {
        $total = 0;
        $weights = [15, 20, 30, 35];

        $result = $this->service->distributeToQuarters($total, $weights);

        $this->assertCount(4, $result);
        $this->assertEquals($total, array_sum($result));
        $this->assertEquals([0, 0, 0, 0], $result);
    }

    public function test_compute_branch_actuals_with_no_data(): void
    {
        $branch = Branch::factory()->create();
        $year = 2024;

        $result = $this->service->computeBranchActuals($branch->id, $year);

        $this->assertEquals([
            'attendance' => 0,
            'guests' => 0,
            'converts' => 0,
        ], $result);
    }

    public function test_compute_branch_actuals_with_event_reports(): void
    {
        $branch = Branch::factory()->create();
        $event = Event::factory()->create(['branch_id' => $branch->id]);

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

        $result = $this->service->computeBranchActuals($branch->id, 2024);

        $this->assertEquals([
            'attendance' => 160 + 140, // Total attendance from both reports
            'guests' => 15 + 11, // Total guests from both reports
            'converts' => 5 + 3, // Total converts from both reports
        ], $result);
    }

    public function test_compute_branch_actuals_with_custom_date_range(): void
    {
        $branch = Branch::factory()->create();
        $event = Event::factory()->create(['branch_id' => $branch->id]);

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

        $result = $this->service->computeBranchActuals($branch->id, 2024, '2024-01-01', '2024-02-28');

        // Should only include the first report
        $this->assertEquals([
            'attendance' => 160, // Only first report
            'guests' => 10, // Only first report
            'converts' => 3, // Only first report
        ], $result);
    }

    public function test_compute_network_actuals_with_no_data(): void
    {
        $year = 2024;

        $result = $this->service->computeNetworkActuals($year);

        $this->assertEquals([
            'attendance' => 0,
            'guests' => 0,
            'converts' => 0,
        ], $result);
    }

    public function test_compute_network_actuals_with_multiple_branches(): void
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

        $result = $this->service->computeNetworkActuals(2024);

        $this->assertEquals([
            'attendance' => 160 + 125, // Total from both branches
            'guests' => 10 + 8, // Total from both branches
            'converts' => 3 + 2, // Total from both branches
        ], $result);
    }

    public function test_delta_percent_with_zero_previous(): void
    {
        $result = ProjectionService::deltaPercent(100, 0);
        $this->assertEquals(100.0, $result);

        $result = ProjectionService::deltaPercent(0, 0);
        $this->assertEquals(0.0, $result);
    }

    public function test_delta_percent_with_normal_values(): void
    {
        $result = ProjectionService::deltaPercent(120, 100);
        $this->assertEquals(20.0, $result);

        $result = ProjectionService::deltaPercent(80, 100);
        $this->assertEquals(-20.0, $result);

        $result = ProjectionService::deltaPercent(105, 100);
        $this->assertEquals(5.0, $result);
    }

    public function test_quarter_dates(): void
    {
        $result = $this->service->quarterDates(2024, 1);
        $this->assertEquals('2024-01-01', $result['start']);
        $this->assertEquals('2024-03-31', $result['end']);

        $result = $this->service->quarterDates(2024, 2);
        $this->assertEquals('2024-04-01', $result['start']);
        $this->assertEquals('2024-06-30', $result['end']);

        $result = $this->service->quarterDates(2024, 3);
        $this->assertEquals('2024-07-01', $result['start']);
        $this->assertEquals('2024-09-30', $result['end']);

        $result = $this->service->quarterDates(2024, 4);
        $this->assertEquals('2024-10-01', $result['start']);
        $this->assertEquals('2024-12-31', $result['end']);
    }

    public function test_quarter_comparison(): void
    {
        $branch = Branch::factory()->create();
        $event = Event::factory()->create(['branch_id' => $branch->id]);

        // Create event reports for current year
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

        // Create event reports for previous year
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

        $result = $this->service->quarterComparison($branch->id, 2024);

        $this->assertCount(4, $result);

        // Check Q1 data
        $q1 = $result[0];
        $this->assertEquals('Q1', $q1['quarter']);
        $this->assertEquals(160, $q1['current']['attendance']);
        $this->assertEquals(125, $q1['previous']['attendance']);
        $this->assertEquals(28.0, $q1['delta']['attendance']); // (160-125)/125 * 100
    }
}
