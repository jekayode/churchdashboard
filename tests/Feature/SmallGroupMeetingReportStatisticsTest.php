<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\SmallGroup;
use App\Models\SmallGroupMeetingReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SmallGroupMeetingReportStatisticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function statistics_endpoint_filters_by_date_from_and_date_to_when_period_is_custom(): void
    {
        $superAdminRole = Role::factory()->create(['name' => 'super_admin']);
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach($superAdminRole);

        $branch = Branch::factory()->create();
        $smallGroup = SmallGroup::factory()->create(['branch_id' => $branch->id]);

        SmallGroupMeetingReport::factory()->create([
            'small_group_id' => $smallGroup->id,
            'meeting_date' => '2025-06-15',
            'total_attendance' => 10,
            'male_attendance' => 5,
            'female_attendance' => 5,
            'children_attendance' => 0,
            'first_time_guests' => 0,
            'converts' => 0,
        ]);

        SmallGroupMeetingReport::factory()->create([
            'small_group_id' => $smallGroup->id,
            'meeting_date' => '2025-01-10',
            'total_attendance' => 90,
            'male_attendance' => 45,
            'female_attendance' => 45,
            'children_attendance' => 0,
            'first_time_guests' => 0,
            'converts' => 0,
        ]);

        $this->actingAs($superAdmin, 'sanctum');

        $response = $this->getJson('/api/small-group-reports/statistics?'.http_build_query([
            'period' => 'custom',
            'date_from' => '2025-06-01',
            'date_to' => '2025-06-30',
            'branch_id' => $branch->id,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_attendance', 10)
            ->assertJsonPath('data.total_reports', 1);
    }
}
