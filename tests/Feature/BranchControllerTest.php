<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class BranchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $superAdmin;
    private User $branchPastor;
    private User $regularUser;
    private Role $superAdminRole;
    private Role $branchPastorRole;
    private Role $memberRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->superAdminRole = Role::factory()->create(['name' => 'super_admin']);
        $this->branchPastorRole = Role::factory()->create(['name' => 'branch_pastor']);
        $this->memberRole = Role::factory()->create(['name' => 'church_member']);

        // Create users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->branchPastor = User::factory()->create();
        $this->branchPastor->assignRole('branch_pastor');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('church_member');
    }

    public function test_super_admin_can_list_all_branches(): void
    {
        Sanctum::actingAs($this->superAdmin);

        Branch::factory()->count(3)->create();

        $response = $this->getJson('/api/branches');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'venue',
                            'service_time',
                            'status',
                            'pastor',
                            'members_count',
                            'ministries_count',
                            'small_groups_count',
                            'events_count',
                        ],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
                'message',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_branches_can_be_filtered_and_searched(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $activeBranch = Branch::factory()->create([
            'name' => 'Main Campus',
            'status' => 'active',
        ]);

        $inactiveBranch = Branch::factory()->create([
            'name' => 'Secondary Campus',
            'status' => 'inactive',
        ]);

        // Test status filter
        $response = $this->getJson('/api/branches?status=active');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals($activeBranch->name, $response->json('data.data.0.name'));

        // Test search
        $response = $this->getJson('/api/branches?search=Main');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals($activeBranch->name, $response->json('data.data.0.name'));
    }

    public function test_super_admin_can_create_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branchData = [
            'name' => 'New Branch',
            'venue' => '123 Church Street',
            'service_time' => '10:00 AM',
            'phone' => '+1-555-0123',
            'email' => 'newbranch@church.com',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/branches', $branchData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Branch created successfully.',
            ]);

        $this->assertDatabaseHas('branches', [
            'name' => 'New Branch',
            'venue' => '123 Church Street',
            'email' => 'newbranch@church.com',
        ]);
    }

    public function test_branch_creation_validates_required_fields(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/branches', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'venue', 'service_time', 'status']);
    }

    public function test_branch_creation_validates_unique_name(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $existingBranch = Branch::factory()->create(['name' => 'Existing Branch']);

        $response = $this->postJson('/api/branches', [
            'name' => 'Existing Branch',
            'venue' => '123 New Street',
            'service_time' => '10:00 AM',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_super_admin_can_view_specific_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();

        $response = $this->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Branch retrieved successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'venue',
                    'service_time',
                    'status',
                    'pastor',
                    'members',
                    'ministries',
                    'small_groups',
                    'events',
                ],
            ]);
    }

    public function test_super_admin_can_update_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();

        $updateData = [
            'name' => 'Updated Branch Name',
            'venue' => 'Updated Venue',
            'service_time' => '11:00 AM',
            'status' => 'inactive',
        ];

        $response = $this->putJson("/api/branches/{$branch->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Branch updated successfully.',
            ]);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Updated Branch Name',
            'venue' => 'Updated Venue',
            'status' => 'inactive',
        ]);
    }

    public function test_super_admin_can_delete_empty_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();

        $response = $this->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Branch deleted successfully.',
            ]);

        $this->assertSoftDeleted('branches', ['id' => $branch->id]);
    }

    public function test_cannot_delete_branch_with_members(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();
        $branch->members()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1-555-0123',
            'member_status' => 'member',
        ]);

        $response = $this->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Cannot delete branch with existing members, ministries, small groups, or events.',
            ]);

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_super_admin_can_assign_pastor_to_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();

        $response = $this->postJson("/api/branches/{$branch->id}/assign-pastor", [
            'pastor_id' => $this->branchPastor->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Pastor assigned successfully.',
            ]);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'pastor_id' => $this->branchPastor->id,
        ]);
    }

    public function test_cannot_assign_non_pastor_to_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create();

        $response = $this->postJson("/api/branches/{$branch->id}/assign-pastor", [
            'pastor_id' => $this->regularUser->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Selected user is not a valid pastor.',
            ]);
    }

    public function test_super_admin_can_remove_pastor_from_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branch = Branch::factory()->create(['pastor_id' => $this->branchPastor->id]);

        $response = $this->deleteJson("/api/branches/{$branch->id}/remove-pastor");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Pastor removed successfully.',
            ]);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'pastor_id' => null,
        ]);
    }

    public function test_can_get_available_pastors(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Create additional pastors
        $pastor1 = User::factory()->create();
        $pastor1->assignRole('branch_pastor');

        $pastor2 = User::factory()->create();
        $pastor2->assignRole('branch_pastor');

        $response = $this->getJson('/api/branches/pastors/available');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
                'message',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertGreaterThanOrEqual(3, count($response->json('data'))); // Including branchPastor from setUp
    }

    public function test_branch_pastor_can_view_own_branch(): void
    {
        // Create a fresh branch pastor for this test without existing role conflicts
        $branchPastor = User::factory()->create();
        $branch = Branch::factory()->create(['pastor_id' => $branchPastor->id]);
        $branchPastor->assignRole('branch_pastor', $branch->id);

        Sanctum::actingAs($branchPastor);

        $response = $this->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(200);
    }

    public function test_branch_pastor_cannot_view_other_branch(): void
    {
        $ownBranch = Branch::factory()->create(['pastor_id' => $this->branchPastor->id]);
        $this->branchPastor->assignRole('branch_pastor', $ownBranch->id);

        $otherBranch = Branch::factory()->create();

        Sanctum::actingAs($this->branchPastor);

        $response = $this->getJson("/api/branches/{$otherBranch->id}");

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_create_branch(): void
    {
        Sanctum::actingAs($this->regularUser);

        $branchData = [
            'name' => 'Unauthorized Branch',
            'venue' => '123 Street',
            'service_time' => '10:00 AM',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/branches', $branchData);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_access_branches(): void
    {
        $response = $this->getJson('/api/branches');

        $response->assertStatus(401);
    }

    public function test_branch_creation_with_pastor_assignment(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $branchData = [
            'name' => 'Branch with Pastor',
            'venue' => '456 Church Avenue',
            'service_time' => '9:00 AM',
            'pastor_id' => $this->branchPastor->id,
            'status' => 'active',
        ];

        $response = $this->postJson('/api/branches', $branchData);

        $response->assertStatus(201);

        $branch = Branch::latest()->first();
        $this->assertEquals($this->branchPastor->id, $branch->pastor_id);
    }

    public function test_branch_update_changes_pastor_assignment(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $oldPastor = User::factory()->create();
        $oldPastor->assignRole('branch_pastor');

        $newPastor = User::factory()->create();
        $newPastor->assignRole('branch_pastor');

        $branch = Branch::factory()->create(['pastor_id' => $oldPastor->id]);

        $response = $this->putJson("/api/branches/{$branch->id}", [
            'name' => $branch->name,
            'venue' => $branch->venue,
            'service_time' => $branch->service_time,
            'pastor_id' => $newPastor->id,
            'status' => $branch->status,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'pastor_id' => $newPastor->id,
        ]);
    }
} 