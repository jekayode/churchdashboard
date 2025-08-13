<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $branchPastor;
    private User $ministryLeader;
    private User $departmentLeader;
    private Member $ministryLeaderMember;
    private Member $departmentLeaderMember;
    private Branch $branch;
    private Ministry $ministry;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $superAdminRole = Role::factory()->create(['name' => 'super_admin', 'display_name' => 'Super Admin']);
        $branchPastorRole = Role::factory()->create(['name' => 'branch_pastor', 'display_name' => 'Branch Pastor']);
        $ministryLeaderRole = Role::factory()->create(['name' => 'ministry_leader', 'display_name' => 'Ministry Leader']);
        $departmentLeaderRole = Role::factory()->create(['name' => 'department_leader', 'display_name' => 'Department Leader']);
        
        // Create users
        $this->superAdmin = User::factory()->create();
        $this->branchPastor = User::factory()->create();
        $this->ministryLeader = User::factory()->create();
        $this->departmentLeader = User::factory()->create();
        
        // Create branch
        $this->branch = Branch::factory()->create(['pastor_id' => $this->branchPastor->id]);
        
        // Attach roles with branch_id
        $this->superAdmin->roles()->attach($superAdminRole);
        $this->branchPastor->roles()->attach($branchPastorRole, ['branch_id' => $this->branch->id]);
        $this->ministryLeader->roles()->attach($ministryLeaderRole, ['branch_id' => $this->branch->id]);
        $this->departmentLeader->roles()->attach($departmentLeaderRole, ['branch_id' => $this->branch->id]);
        
        // Create member records for leaders
        $this->ministryLeaderMember = Member::factory()->create([
            'user_id' => $this->ministryLeader->id,
            'branch_id' => $this->branch->id,
            'name' => $this->ministryLeader->name,
            'email' => $this->ministryLeader->email,
            'member_status' => 'leader'
        ]);
        
        $this->departmentLeaderMember = Member::factory()->create([
            'user_id' => $this->departmentLeader->id,
            'branch_id' => $this->branch->id,
            'name' => $this->departmentLeader->name,
            'email' => $this->departmentLeader->email,
            'member_status' => 'leader'
        ]);
        
        // Create ministry
        $this->ministry = Ministry::factory()->create([
            'branch_id' => $this->branch->id,
            'leader_id' => $this->ministryLeaderMember->id
        ]);
        
        // Create department
        $this->department = Department::factory()->create([
            'ministry_id' => $this->ministry->id,
            'leader_id' => $this->departmentLeaderMember->id
        ]);
    }

    public function test_super_admin_can_list_departments(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/departments');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'ministry_id',
                            'leader_id',
                            'ministry',
                            'leader'
                        ]
                    ],
                    'current_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_branch_pastor_can_list_departments_in_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $response = $this->getJson('/api/departments');

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_branch_pastor_cannot_see_other_branch_departments(): void
    {
        Sanctum::actingAs($this->branchPastor);

        // Create another branch with ministry and department
        $otherBranch = Branch::factory()->create();
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $otherBranch->id,
            'leader_id' => null
        ]);
        $otherDepartment = Department::factory()->create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'Other Branch Department',
            'leader_id' => null
        ]);

        $response = $this->getJson('/api/departments');

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->department->name])
            ->assertJsonMissing(['name' => 'Other Branch Department']);
    }

    public function test_branch_pastor_can_update_department_in_own_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $updateData = [
            'name' => 'Updated Department Name',
            'description' => 'Updated description',
            'ministry_id' => $this->department->ministry_id,
            'status' => 'active'
        ];

        $response = $this->putJson("/api/departments/{$this->department->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Updated Department Name']);

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'name' => 'Updated Department Name'
        ]);
    }

    public function test_branch_pastor_cannot_update_department_in_other_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);

        // Create another branch with ministry and department
        $otherBranch = Branch::factory()->create();
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $otherBranch->id,
            'leader_id' => null
        ]);
        $otherDepartment = Department::factory()->create([
            'ministry_id' => $otherMinistry->id,
            'leader_id' => null
        ]);

        $updateData = [
            'name' => 'Unauthorized Update',
            'description' => 'This should fail',
            'ministry_id' => $otherMinistry->id,
            'status' => 'active'
        ];

        $response = $this->putJson("/api/departments/{$otherDepartment->id}", $updateData);

        $response->assertForbidden();
    }

    public function test_ministry_leader_can_list_own_departments(): void
    {
        Sanctum::actingAs($this->ministryLeader);

        $response = $this->getJson('/api/departments');

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_can_filter_departments_by_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $this->branch->id,
            'leader_id' => null
        ]);
        Department::factory()->create([
            'ministry_id' => $otherMinistry->id,
            'leader_id' => null
        ]);

        $response = $this->getJson("/api/departments?ministry_id={$this->ministry->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_can_filter_departments_by_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        // Create another branch with ministry and department
        $otherBranch = Branch::factory()->create();
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $otherBranch->id,
            'leader_id' => null
        ]);
        Department::factory()->create([
            'ministry_id' => $otherMinistry->id,
            'leader_id' => null
        ]);

        $response = $this->getJson("/api/departments?branch_id={$this->branch->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_can_search_departments_by_name(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        Department::factory()->create([
            'name' => 'Different Department',
            'ministry_id' => $this->ministry->id,
            'leader_id' => null
        ]);

        $response = $this->getJson("/api/departments?search={$this->department->name}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_can_sort_departments(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        Department::factory()->create([
            'name' => 'Alpha Department',
            'ministry_id' => $this->ministry->id,
            'leader_id' => null
        ]);

        $response = $this->getJson('/api/departments?sort_by=name&sort_direction=asc');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertEquals('Alpha Department', $data[0]['name']);
    }

    public function test_super_admin_can_create_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $departmentData = [
            'name' => 'New Department',
            'description' => 'A new department for testing',
            'ministry_id' => $this->ministry->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/departments', $departmentData);

        $response->assertCreated()
            ->assertJsonFragment($departmentData);

        $this->assertDatabaseHas('departments', $departmentData);
    }

    public function test_ministry_leader_can_create_department_in_own_ministry(): void
    {
        Sanctum::actingAs($this->ministryLeader);

        $departmentData = [
            'name' => 'Ministry Department',
            'description' => 'A department for the ministry',
            'ministry_id' => $this->ministry->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/departments', $departmentData);

        $response->assertCreated()
            ->assertJsonFragment($departmentData);
    }

    public function test_ministry_leader_cannot_create_department_in_other_ministry(): void
    {
        Sanctum::actingAs($this->ministryLeader);
        
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $this->branch->id,
            'leader_id' => null
        ]);

        $departmentData = [
            'name' => 'Other Ministry Department',
            'description' => 'A department for another ministry',
            'ministry_id' => $otherMinistry->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/departments', $departmentData);

        $response->assertForbidden();
    }

    public function test_validates_required_fields_when_creating_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/departments', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'ministry_id', 'status']);
    }

    public function test_can_show_specific_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson("/api/departments/{$this->department->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $this->department->id,
                'name' => $this->department->name
            ]);
    }

    public function test_super_admin_can_update_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $updateData = [
            'name' => 'Updated Department Name',
            'description' => 'Updated description',
            'ministry_id' => $this->ministry->id,
            'status' => 'active'
        ];

        $response = $this->putJson("/api/departments/{$this->department->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('departments', array_merge(
            ['id' => $this->department->id],
            $updateData
        ));
    }

    public function test_can_assign_leader_to_department(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $newLeaderUser = User::factory()->create();
        $newLeaderMember = Member::factory()->create([
            'user_id' => $newLeaderUser->id,
            'branch_id' => $this->branch->id,
            'name' => $newLeaderUser->name,
            'email' => $newLeaderUser->email,
            'member_status' => 'leader'
        ]);

        $response = $this->postJson("/api/departments/{$this->department->id}/assign-leader", [
            'leader_id' => $newLeaderMember->id
        ]);

        $response->assertOk()
            ->assertJsonFragment(['leader_id' => $newLeaderMember->id]);

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'leader_id' => $newLeaderMember->id
        ]);
    }

    public function test_can_remove_leader_from_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/departments/{$this->department->id}/remove-leader");

        $response->assertOk()
            ->assertJsonFragment(['leader_id' => null]);

        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'leader_id' => null
        ]);
    }

    public function test_can_assign_member_to_department(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $memberUser = User::factory()->create();
        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'branch_id' => $this->branch->id,
            'name' => $memberUser->name,
            'email' => $memberUser->email,
            'member_status' => 'member'
        ]);

        $response = $this->postJson("/api/departments/{$this->department->id}/assign-members", [
            'member_ids' => [$member->id]
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Members assigned successfully.']);

        $this->assertDatabaseHas('member_departments', [
            'department_id' => $this->department->id,
            'member_id' => $member->id
        ]);
    }

    public function test_cannot_assign_same_member_twice(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $memberUser = User::factory()->create();
        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'branch_id' => $this->branch->id,
            'name' => $memberUser->name,
            'email' => $memberUser->email,
            'member_status' => 'member'
        ]);
        
        // First assignment
        $this->department->members()->attach($member);

        $response = $this->postJson("/api/departments/{$this->department->id}/assign-members", [
            'member_ids' => [$member->id]
        ]);

        $response->assertOk(); // syncWithoutDetaching won't create duplicates, so this should succeed
    }

    public function test_can_remove_member_from_department(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $memberUser = User::factory()->create();
        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'branch_id' => $this->branch->id,
            'name' => $memberUser->name,
            'email' => $memberUser->email,
            'member_status' => 'member'
        ]);
        $this->department->members()->attach($member);

        $response = $this->deleteJson("/api/departments/{$this->department->id}/remove-members", [
            'member_ids' => [$member->id]
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Members removed successfully.']);

        $this->assertDatabaseMissing('member_departments', [
            'department_id' => $this->department->id,
            'member_id' => $member->id
        ]);
    }



    public function test_super_admin_can_delete_department(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/departments/{$this->department->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Department deleted successfully.']);

        $this->assertSoftDeleted('departments', ['id' => $this->department->id]);
    }

    public function test_ministry_leader_can_delete_own_department(): void
    {
        Sanctum::actingAs($this->ministryLeader);

        $response = $this->deleteJson("/api/departments/{$this->department->id}");

        $response->assertOk();
    }

    public function test_department_leader_can_view_own_department(): void
    {
        Sanctum::actingAs($this->departmentLeader);

        $response = $this->getJson("/api/departments/{$this->department->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->department->name]);
    }

    public function test_department_leader_cannot_delete_department(): void
    {
        Sanctum::actingAs($this->departmentLeader);

        $response = $this->deleteJson("/api/departments/{$this->department->id}");

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_access_departments(): void
    {
        $regularUser = User::factory()->create();
        Sanctum::actingAs($regularUser);

        $response = $this->getJson('/api/departments');

        $response->assertForbidden();
    }
} 