<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MinistryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $branchPastor;
    private User $ministryLeader;
    private Member $ministryLeaderMember;
    private Branch $branch;
    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $superAdminRole = Role::factory()->create(['name' => 'super_admin', 'display_name' => 'Super Admin']);
        $branchPastorRole = Role::factory()->create(['name' => 'branch_pastor', 'display_name' => 'Branch Pastor']);
        $ministryLeaderRole = Role::factory()->create(['name' => 'ministry_leader', 'display_name' => 'Ministry Leader']);
        
        // Create users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->roles()->attach($superAdminRole);
        
        $this->branchPastor = User::factory()->create();
        $this->ministryLeader = User::factory()->create();
        
        // Create branch
        $this->branch = Branch::factory()->create(['pastor_id' => $this->branchPastor->id]);
        
        // Attach roles with branch_id
        $this->branchPastor->roles()->attach($branchPastorRole, ['branch_id' => $this->branch->id]);
        $this->ministryLeader->roles()->attach($ministryLeaderRole, ['branch_id' => $this->branch->id]);
        
        // Create member record for ministry leader
        $this->ministryLeaderMember = Member::factory()->create([
            'user_id' => $this->ministryLeader->id,
            'branch_id' => $this->branch->id,
            'name' => $this->ministryLeader->name,
            'email' => $this->ministryLeader->email,
            'member_status' => 'leader'
        ]);
        
        // Create ministry
        $this->ministry = Ministry::factory()->create([
            'branch_id' => $this->branch->id,
            'leader_id' => $this->ministryLeaderMember->id
        ]);
    }

    public function test_super_admin_can_list_ministries(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/ministries');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'branch_id',
                            'leader_id',
                            'branch',
                            'leader'
                        ]
                    ],
                    'current_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_branch_pastor_can_list_own_ministries(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $response = $this->getJson('/api/ministries');

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->ministry->name]);
    }

    public function test_branch_pastor_cannot_see_other_branch_ministries(): void
    {
        Sanctum::actingAs($this->branchPastor);

        // Create another branch with ministry
        $otherBranch = Branch::factory()->create();
        $otherMinistry = Ministry::factory()->create([
            'branch_id' => $otherBranch->id,
            'name' => 'Other Branch Ministry',
            'leader_id' => null
        ]);

        $response = $this->getJson('/api/ministries');

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->ministry->name])
            ->assertJsonMissing(['name' => 'Other Branch Ministry']);
    }

    public function test_can_filter_ministries_by_branch(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $otherBranch = Branch::factory()->create();
        Ministry::factory()->create([
            'branch_id' => $otherBranch->id,
            'leader_id' => null
        ]);

        $response = $this->getJson("/api/ministries?branch_id={$this->branch->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => $this->ministry->name]);
    }

    public function test_can_search_ministries_by_name(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        Ministry::factory()->create([
            'name' => 'Different Ministry',
            'branch_id' => $this->branch->id,
            'leader_id' => null
        ]);

        $response = $this->getJson("/api/ministries?search={$this->ministry->name}");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['name' => $this->ministry->name]);
    }

    public function test_can_sort_ministries(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        Ministry::factory()->create([
            'name' => 'Alpha Ministry',
            'branch_id' => $this->branch->id,
            'leader_id' => null
        ]);

        $response = $this->getJson('/api/ministries?sort_by=name&sort_direction=asc');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertEquals('Alpha Ministry', $data[0]['name']);
    }

    public function test_super_admin_can_create_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $ministryData = [
            'name' => 'New Ministry',
            'description' => 'A new ministry for testing',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/ministries', $ministryData);

        $response->assertCreated()
            ->assertJsonFragment($ministryData);

        $this->assertDatabaseHas('ministries', $ministryData);
    }

    public function test_branch_pastor_can_create_ministry_in_own_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $ministryData = [
            'name' => 'Branch Ministry',
            'description' => 'A ministry for the branch',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/ministries', $ministryData);

        $response->assertCreated()
            ->assertJsonFragment($ministryData);
    }

    public function test_branch_pastor_cannot_create_ministry_in_other_branch(): void
    {
        Sanctum::actingAs($this->branchPastor);
        
        $otherBranch = Branch::factory()->create();

        $ministryData = [
            'name' => 'Other Branch Ministry',
            'description' => 'A ministry for another branch',
            'branch_id' => $otherBranch->id,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/ministries', $ministryData);

        $response->assertForbidden();
    }

    public function test_validates_required_fields_when_creating_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/ministries', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'branch_id', 'status']);
    }

    public function test_can_show_specific_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson("/api/ministries/{$this->ministry->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $this->ministry->id,
                'name' => $this->ministry->name
            ]);
    }

    public function test_super_admin_can_update_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $updateData = [
            'name' => 'Updated Ministry Name',
            'description' => 'Updated description',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ];

        $response = $this->putJson("/api/ministries/{$this->ministry->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('ministries', array_merge(
            ['id' => $this->ministry->id],
            $updateData
        ));
    }

    public function test_can_assign_leader_to_ministry(): void
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

        $response = $this->postJson("/api/ministries/{$this->ministry->id}/assign-leader", [
            'leader_id' => $newLeaderMember->id
        ]);

        $response->assertOk()
            ->assertJsonFragment(['leader_id' => $newLeaderMember->id]);

        $this->assertDatabaseHas('ministries', [
            'id' => $this->ministry->id,
            'leader_id' => $newLeaderMember->id
        ]);
    }

    public function test_can_remove_leader_from_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/ministries/{$this->ministry->id}/remove-leader");

        $response->assertOk()
            ->assertJsonFragment(['leader_id' => null]);

        $this->assertDatabaseHas('ministries', [
            'id' => $this->ministry->id,
            'leader_id' => null
        ]);
    }

    public function test_can_get_available_leaders(): void
    {
        Sanctum::actingAs($this->superAdmin);
        
        $availableLeaderUser = User::factory()->create();
        Member::factory()->create([
            'user_id' => $availableLeaderUser->id,
            'branch_id' => $this->branch->id,
            'name' => $availableLeaderUser->name,
            'email' => $availableLeaderUser->email,
            'member_status' => 'member'
        ]);

        $response = $this->getJson("/api/ministries/leaders/available");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ]);
    }

    public function test_super_admin_can_delete_ministry(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->deleteJson("/api/ministries/{$this->ministry->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Ministry deleted successfully.']);

        $this->assertSoftDeleted('ministries', ['id' => $this->ministry->id]);
    }

    public function test_branch_pastor_can_delete_own_ministry(): void
    {
        Sanctum::actingAs($this->branchPastor);

        $response = $this->deleteJson("/api/ministries/{$this->ministry->id}");

        $response->assertOk();
    }

    public function test_ministry_leader_can_view_own_ministry(): void
    {
        Sanctum::actingAs($this->ministryLeader);

        $response = $this->getJson("/api/ministries/{$this->ministry->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $this->ministry->name]);
    }

    public function test_ministry_leader_cannot_delete_ministry(): void
    {
        Sanctum::actingAs($this->ministryLeader);

        $response = $this->deleteJson("/api/ministries/{$this->ministry->id}");

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_access_ministries(): void
    {
        $regularUser = User::factory()->create();
        Sanctum::actingAs($regularUser);

        $response = $this->getJson('/api/ministries');

        $response->assertForbidden();
    }
} 