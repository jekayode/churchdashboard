<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Projection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class ProjectionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $superAdmin;

    private User $branchPastor;

    private User $regularUser;

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

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('church_member');

        // Create test branch
        $this->branch = Branch::factory()->create([
            'pastor_id' => $this->branchPastor->id,
        ]);

        // Assign branch pastor role with branch context
        $this->branchPastor->assignRole('branch_pastor', $this->branch->id);
    }

    /** @test */
    public function super_admin_can_view_all_projections(): void
    {
        // Create projections for different branches
        $projection1 = Projection::factory()->create(['branch_id' => $this->branch->id]);
        $projection2 = Projection::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'branch_id',
                            'year',
                            'attendance_target',
                            'converts_target',
                            'leaders_target',
                            'volunteers_target',
                            'status',
                            'is_current_year',
                            'branch' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data.data'));
    }

    /** @test */
    public function branch_pastor_can_only_view_their_branch_projections(): void
    {
        // Create projection for pastor's branch
        $ownProjection = Projection::factory()->create(['branch_id' => $this->branch->id]);

        // Create projection for another branch
        $otherProjection = Projection::factory()->create();

        $response = $this->actingAs($this->branchPastor)
            ->getJson('/api/projections');

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertEquals($ownProjection->id, $projections[0]['id']);
    }

    /** @test */
    public function regular_user_cannot_view_projections(): void
    {
        Projection::factory()->create();

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/projections');

        $response->assertForbidden();
    }

    /** @test */
    public function super_admin_can_create_projection_for_any_branch(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'year' => 2030, // Use a specific year to avoid conflicts
            'attendance_target' => 500,
            'converts_target' => 50,
            'leaders_target' => 25,
            'volunteers_target' => 75,
            'quarterly_attendance' => [100, 125, 150, 125],
            'quarterly_converts' => [10, 12, 15, 13],
            'quarterly_leaders' => [5, 6, 8, 6],
            'quarterly_volunteers' => [15, 18, 22, 20],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections', $data);

        $response->assertCreated()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection created successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'branch_id' => $this->branch->id,
            'year' => 2030,
            'attendance_target' => 500,
            'status' => 'draft',
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function branch_pastor_can_create_projection_for_their_branch(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'year' => 2031, // Use a different year to avoid conflicts
            'attendance_target' => 300,
            'converts_target' => 30,
            'leaders_target' => 15,
            'volunteers_target' => 45,
            'quarterly_attendance' => [60, 75, 90, 75],
            'quarterly_converts' => [6, 7, 9, 8],
            'quarterly_leaders' => [3, 4, 5, 3],
            'quarterly_volunteers' => [9, 11, 14, 11],
        ];

        $response = $this->actingAs($this->branchPastor)
            ->postJson('/api/projections', $data);

        $response->assertCreated();

        $this->assertDatabaseHas('projections', [
            'branch_id' => $this->branch->id,
            'year' => 2031,
            'created_by' => $this->branchPastor->id,
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_create_projection_for_other_branch(): void
    {
        $otherBranch = Branch::factory()->create();

        $data = [
            'branch_id' => $otherBranch->id,
            'year' => 2032, // Use a different year to avoid conflicts
            'attendance_target' => 300,
            'converts_target' => 30,
            'leaders_target' => 15,
            'volunteers_target' => 45,
        ];

        $response = $this->actingAs($this->branchPastor)
            ->postJson('/api/projections', $data);

        $response->assertForbidden();
    }

    /** @test */
    public function cannot_create_duplicate_projection_for_same_branch_and_year(): void
    {
        // Create existing projection
        Projection::factory()->create([
            'branch_id' => $this->branch->id,
            'year' => 2025,
        ]);

        $data = [
            'branch_id' => $this->branch->id,
            'year' => 2025,
            'attendance_target' => 300,
            'converts_target' => 30,
            'leaders_target' => 15,
            'volunteers_target' => 45,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['year']);
    }

    /** @test */
    public function can_view_specific_projection(): void
    {
        $projection = Projection::factory()->create([
            'branch_id' => $this->branch->id,
            'created_by' => $this->branchPastor->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/projections/{$projection->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $projection->id,
                'branch_id' => $this->branch->id,
                'year' => $projection->year,
            ]);
    }

    /** @test */
    public function can_update_draft_projection(): void
    {
        $projection = Projection::factory()->draft()->create([
            'branch_id' => $this->branch->id,
            'created_by' => $this->branchPastor->id,
        ]);

        $updateData = [
            'attendance_target' => 600,
            'converts_target' => 60,
        ];

        $response = $this->actingAs($this->branchPastor)
            ->putJson("/api/projections/{$projection->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection updated successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'attendance_target' => 600,
            'converts_target' => 60,
        ]);
    }

    /** @test */
    public function super_admin_can_update_approved_projection(): void
    {
        $projection = Projection::factory()->approved()->create([
            'branch_id' => $this->branch->id,
        ]);

        $updateData = [
            'attendance_target' => 600,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/projections/{$projection->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection updated successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'attendance_target' => 600,
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_update_approved_projection(): void
    {
        $projection = Projection::factory()->approved()->create([
            'branch_id' => $this->branch->id,
        ]);

        $updateData = [
            'attendance_target' => 600,
        ];

        $response = $this->actingAs($this->branchPastor)
            ->putJson("/api/projections/{$projection->id}", $updateData);

        $response->assertForbidden();
    }

    /** @test */
    public function branch_pastor_can_update_rejected_projection(): void
    {
        $projection = Projection::factory()->rejected()->create([
            'branch_id' => $this->branch->id,
        ]);

        $updateData = [
            'attendance_target' => 600,
        ];

        $response = $this->actingAs($this->branchPastor)
            ->putJson("/api/projections/{$projection->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection updated successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'attendance_target' => 600,
        ]);
    }

    /** @test */
    public function can_delete_draft_projection(): void
    {
        $projection = Projection::factory()->draft()->create([
            'branch_id' => $this->branch->id,
            'created_by' => $this->branchPastor->id,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->deleteJson("/api/projections/{$projection->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection deleted successfully.',
            ]);

        $this->assertSoftDeleted('projections', [
            'id' => $projection->id,
        ]);
    }

    /** @test */
    public function super_admin_can_delete_approved_projection(): void
    {
        $projection = Projection::factory()->approved()->create([
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/projections/{$projection->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection deleted successfully.',
            ]);

        $this->assertSoftDeleted('projections', [
            'id' => $projection->id,
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_delete_approved_projection(): void
    {
        $projection = Projection::factory()->approved()->create([
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->deleteJson("/api/projections/{$projection->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function can_submit_draft_projection_for_review(): void
    {
        $projection = Projection::factory()->draft()->create([
            'branch_id' => $this->branch->id,
            'created_by' => $this->branchPastor->id,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->postJson("/api/projections/{$projection->id}/submit-for-review");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection submitted for review successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'status' => 'in_review',
        ]);
    }

    /** @test */
    public function super_admin_can_approve_projection(): void
    {
        $projection = Projection::factory()->inReview()->create([
            'branch_id' => $this->branch->id,
        ]);

        $approvalData = [
            'approval_notes' => 'Looks good to proceed.',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/projections/{$projection->id}/approve", $approvalData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection approved successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'status' => 'approved',
            'approved_by' => $this->superAdmin->id,
            'approval_notes' => 'Looks good to proceed.',
        ]);
    }

    /** @test */
    public function super_admin_can_reject_projection(): void
    {
        $projection = Projection::factory()->inReview()->create([
            'branch_id' => $this->branch->id,
        ]);

        $rejectionData = [
            'rejection_reason' => 'Targets need to be more realistic.',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/projections/{$projection->id}/reject", $rejectionData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection rejected successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'status' => 'rejected',
            'rejection_reason' => 'Targets need to be more realistic.',
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_approve_projection(): void
    {
        $projection = Projection::factory()->inReview()->create([
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->branchPastor)
            ->postJson("/api/projections/{$projection->id}/approve");

        $response->assertForbidden();
    }

    /** @test */
    public function super_admin_can_set_current_year_projection(): void
    {
        $projection = Projection::factory()->approved()->create([
            'branch_id' => $this->branch->id,
            'year' => now()->year,
            'is_current_year' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/projections/{$projection->id}/set-current-year");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Projection set as current year successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $projection->id,
            'is_current_year' => true,
        ]);
    }

    /** @test */
    public function can_get_available_branches(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections/branches/available');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_get_projection_statistics(): void
    {
        // Create various projections with unique years
        Projection::factory()->approved()->create(['branch_id' => $this->branch->id, 'year' => 2033]);
        Projection::factory()->inReview()->create(['branch_id' => $this->branch->id, 'year' => 2034]);
        Projection::factory()->draft()->create(['branch_id' => $this->branch->id, 'year' => 2035]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'approved',
                    'pending',
                    'branches_covered',
                    'current_year_projections',
                ],
            ]);
    }

    /** @test */
    public function projection_validation_requires_all_targets(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'year' => now()->year + 1, // Use unique year
            // Missing required targets
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attendance_target',
                'converts_target',
                'leaders_target',
                'volunteers_target',
            ]);
    }

    /** @test */
    public function projection_validation_requires_positive_targets(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'year' => now()->year + 2, // Use unique year
            'attendance_target' => 0, // Invalid - min is 1
            'converts_target' => -1, // Invalid - min is 0
            'leaders_target' => 10,
            'volunteers_target' => 20,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attendance_target',
                'converts_target',
            ]);
    }

    /** @test */
    public function can_filter_projections_by_year(): void
    {
        $testYear = 2036;
        $testBranch = Branch::factory()->create();

        Projection::factory()->create([
            'branch_id' => $testBranch->id,
            'year' => $testYear,
        ]);

        Projection::factory()->create([
            'branch_id' => $testBranch->id,
            'year' => $testYear + 1,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/projections?year={$testYear}");

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertEquals($testYear, $projections[0]['year']);
    }

    /** @test */
    public function can_filter_projections_by_status(): void
    {
        $testBranch = Branch::factory()->create();
        Projection::factory()->approved()->create(['branch_id' => $testBranch->id, 'year' => 2038]);
        Projection::factory()->draft()->create(['branch_id' => $testBranch->id, 'year' => 2039]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections?status=approved');

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertEquals('approved', $projections[0]['status']);
    }

    /** @test */
    public function can_filter_projections_by_current_year(): void
    {
        $testBranch = Branch::factory()->create();
        Projection::factory()->currentYear()->create(['branch_id' => $testBranch->id, 'year' => now()->year + 3]);
        Projection::factory()->create([
            'branch_id' => $testBranch->id,
            'year' => now()->year + 4,
            'is_current_year' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections?is_current_year=1');

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertTrue($projections[0]['is_current_year']);
    }

    /** @test */
    public function super_admin_can_create_global_projection(): void
    {
        $data = [
            'year' => now()->year + 5,
            'attendance_target' => 1000,
            'converts_target' => 100,
            'leaders_target' => 50,
            'volunteers_target' => 150,
            'is_global' => true,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections/global', $data);

        $response->assertCreated()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Global projection created successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'year' => now()->year + 5,
            'is_global' => true,
            'branch_id' => null,
            'attendance_target' => 1000,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    /** @test */
    public function super_admin_can_update_existing_global_projection(): void
    {
        // Create existing global projection
        $existingProjection = Projection::factory()->create([
            'year' => now()->year + 6,
            'is_global' => true,
            'branch_id' => null,
            'attendance_target' => 500,
        ]);

        $data = [
            'year' => now()->year + 6,
            'attendance_target' => 1200,
            'converts_target' => 120,
            'leaders_target' => 60,
            'volunteers_target' => 180,
            'is_global' => true,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections/global', $data);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Global projection updated successfully.',
            ]);

        $this->assertDatabaseHas('projections', [
            'id' => $existingProjection->id,
            'attendance_target' => 1200,
            'converts_target' => 120,
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_create_global_projection(): void
    {
        $data = [
            'year' => now()->year + 7,
            'attendance_target' => 1000,
            'converts_target' => 100,
            'leaders_target' => 50,
            'volunteers_target' => 150,
            'is_global' => true,
        ];

        $response = $this->actingAs($this->branchPastor)
            ->postJson('/api/projections/global', $data);

        $response->assertForbidden();
    }

    /** @test */
    public function can_filter_projections_by_global_flag(): void
    {
        $testBranch = Branch::factory()->create();

        // Create branch-specific projection
        Projection::factory()->create([
            'branch_id' => $testBranch->id,
            'year' => now()->year + 8,
            'is_global' => false,
        ]);

        // Create global projection
        Projection::factory()->create([
            'year' => now()->year + 9,
            'is_global' => true,
            'branch_id' => null,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections?is_global=true');

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertTrue($projections[0]['is_global']);
        $this->assertNull($projections[0]['branch_id']);
    }

    /** @test */
    public function can_filter_projections_by_branch_specific_flag(): void
    {
        $testBranch = Branch::factory()->create();

        // Create branch-specific projection
        Projection::factory()->create([
            'branch_id' => $testBranch->id,
            'year' => now()->year + 10,
            'is_global' => false,
        ]);

        // Create global projection
        Projection::factory()->create([
            'year' => now()->year + 8,
            'is_global' => true,
            'branch_id' => null,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/projections?is_global=false');

        $response->assertOk();

        $projections = $response->json('data.data');
        $this->assertCount(1, $projections);
        $this->assertFalse($projections[0]['is_global']);
        $this->assertNotNull($projections[0]['branch_id']);
    }

    /** @test */
    public function cannot_create_duplicate_global_projection_for_same_year(): void
    {
        // Create existing global projection
        Projection::factory()->create([
            'year' => now()->year + 9,
            'is_global' => true,
            'branch_id' => null,
        ]);

        $data = [
            'year' => now()->year + 9,
            'attendance_target' => 1000,
            'converts_target' => 100,
            'leaders_target' => 50,
            'volunteers_target' => 150,
            'is_global' => true,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections/global', $data);

        // Should update existing instead of creating duplicate
        $response->assertOk();

        // Should only have one global projection for this year
        $this->assertDatabaseCount('projections', 1);
    }

    /** @test */
    public function global_projection_validation_requires_all_targets(): void
    {
        $data = [
            'year' => now()->year + 10,
            'is_global' => true,
            // Missing required targets
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections/global', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attendance_target',
                'converts_target',
                'leaders_target',
                'volunteers_target',
            ]);
    }

    /** @test */
    public function global_projection_automatically_sets_branch_id_to_null(): void
    {
        $data = [
            'year' => now()->year + 1,
            'attendance_target' => 1000,
            'converts_target' => 100,
            'leaders_target' => 50,
            'volunteers_target' => 150,
            'is_global' => true,
            'branch_id' => $this->branch->id, // This should be overridden
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/projections/global', $data);

        $response->assertCreated();

        $this->assertDatabaseHas('projections', [
            'year' => now()->year + 1,
            'is_global' => true,
            'branch_id' => null, // Should be null even though we passed branch_id
        ]);
    }
}
