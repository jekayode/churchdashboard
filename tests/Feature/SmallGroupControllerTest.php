<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Role;
use App\Models\SmallGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class SmallGroupControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $superAdmin;
    private User $branchPastor1;
    private User $branchPastor2;
    private User $regularUser;
    private Branch $branch1;
    private Branch $branch2;
    private Member $member1;
    private Member $member2;
    private Member $member3;
    private SmallGroup $smallGroup1;
    private SmallGroup $smallGroup2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::factory()->create(['name' => 'super_admin']);
        $branchPastorRole = Role::factory()->create(['name' => 'branch_pastor']);

        // Create branches
        $this->branch1 = Branch::factory()->create(['name' => 'Main Branch']);
        $this->branch2 = Branch::factory()->create(['name' => 'Second Branch']);

        // Create users
        $this->superAdmin = User::factory()->create(['name' => 'Super Admin']);
        $this->superAdmin->roles()->attach($superAdminRole);

        $this->branchPastor1 = User::factory()->create(['name' => 'Branch Pastor 1']);
        $this->branchPastor1->roles()->attach($branchPastorRole, ['branch_id' => $this->branch1->id]);

        $this->branchPastor2 = User::factory()->create(['name' => 'Branch Pastor 2']);
        $this->branchPastor2->roles()->attach($branchPastorRole, ['branch_id' => $this->branch2->id]);

        $this->regularUser = User::factory()->create(['name' => 'Regular User']);

        // Create members
        $this->member1 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'user_id' => $this->branchPastor1->id,
        ]);
        $this->member2 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
        ]);
        $this->member3 = Member::factory()->create([
            'branch_id' => $this->branch2->id,
        ]);

        // Create small groups
        $this->smallGroup1 = SmallGroup::factory()->create([
            'branch_id' => $this->branch1->id,
            'leader_id' => $this->member1->id,
            'name' => 'Prayer Group',
            'status' => 'active',
        ]);
        $this->smallGroup2 = SmallGroup::factory()->create([
            'branch_id' => $this->branch2->id,
            'name' => 'Bible Study',
            'status' => 'active',
        ]);

        // Assign members to small groups
        $this->smallGroup1->members()->attach($this->member1->id, ['joined_at' => now()]);
        $this->smallGroup1->members()->attach($this->member2->id, ['joined_at' => now()]);
    }

    /** @test */
    public function super_admin_can_view_all_small_groups(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson('/api/small-groups');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'status',
                            'branch',
                            'leader',
                            'members',
                            'members_count',
                            'has_leader',
                            'is_active',
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
                'message',
                'filters',
            ])
            ->assertJsonPath('data.data.0.name', 'Bible Study')
            ->assertJsonPath('data.data.1.name', 'Prayer Group');
    }

    /** @test */
    public function branch_pastor_can_only_view_their_branch_small_groups(): void
    {
        $this->actingAs($this->branchPastor1, 'sanctum');

        $response = $this->getJson('/api/small-groups');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Prayer Group')
            ->assertJsonPath('data.data.0.branch.id', $this->branch1->id);
    }

    /** @test */
    public function regular_user_can_view_small_groups(): void
    {
        $this->actingAs($this->regularUser, 'sanctum');

        $response = $this->getJson('/api/small-groups');

        $response->assertOk();
    }

    /** @test */
    public function can_filter_small_groups_by_status(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson('/api/small-groups?status=active');

        $response->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function can_search_small_groups(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson('/api/small-groups?search=Prayer');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Prayer Group');
    }

    /** @test */
    public function super_admin_can_create_small_group_for_any_branch(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $smallGroupData = [
            'name' => 'Youth Group',
            'description' => 'A group for young people',
            'branch_id' => $this->branch1->id,
            'leader_id' => $this->member2->id,
            'meeting_day' => 'Friday',
            'meeting_time' => '19:00',
            'location' => 'Church Hall',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/small-groups', $smallGroupData);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Youth Group')
            ->assertJsonPath('data.branch.id', $this->branch1->id)
            ->assertJsonPath('data.leader.id', $this->member2->id);

        $this->assertDatabaseHas('small_groups', [
            'name' => 'Youth Group',
            'branch_id' => $this->branch1->id,
            'leader_id' => $this->member2->id,
        ]);
    }

    /** @test */
    public function branch_pastor_can_create_small_group_for_their_branch(): void
    {
        $this->actingAs($this->branchPastor1, 'sanctum');

        $smallGroupData = [
            'name' => 'Men Fellowship',
            'description' => 'A group for men',
            'leader_id' => $this->member2->id,
            'meeting_day' => 'Saturday',
            'meeting_time' => '08:00',
            'location' => 'Conference Room',
        ];

        $response = $this->postJson('/api/small-groups', $smallGroupData);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Men Fellowship')
            ->assertJsonPath('data.branch.id', $this->branch1->id);

        $this->assertDatabaseHas('small_groups', [
            'name' => 'Men Fellowship',
            'branch_id' => $this->branch1->id,
        ]);
    }

    /** @test */
    public function cannot_create_duplicate_small_group_name_in_same_branch(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $smallGroupData = [
            'name' => 'Prayer Group', // Same as existing group in branch1
            'branch_id' => $this->branch1->id,
        ];

        $response = $this->postJson('/api/small-groups', $smallGroupData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function can_create_same_small_group_name_in_different_branches(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $smallGroupData = [
            'name' => 'Prayer Group', // Same name but different branch
            'branch_id' => $this->branch2->id,
        ];

        $response = $this->postJson('/api/small-groups', $smallGroupData);

        $response->assertCreated();
    }

    /** @test */
    public function can_view_specific_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson("/api/small-groups/{$this->smallGroup1->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $this->smallGroup1->id)
            ->assertJsonPath('data.name', 'Prayer Group')
            ->assertJsonPath('data.members_count', 2)
            ->assertJsonPath('data.has_leader', true)
            ->assertJsonPath('data.is_active', true);
    }

    /** @test */
    public function branch_pastor_cannot_view_other_branch_small_group(): void
    {
        $this->actingAs($this->branchPastor1, 'sanctum');

        $response = $this->getJson("/api/small-groups/{$this->smallGroup2->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function can_update_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $updateData = [
            'name' => 'Updated Prayer Group',
            'description' => 'Updated description',
            'meeting_day' => 'Sunday',
            'meeting_time' => '15:00',
        ];

        $response = $this->putJson("/api/small-groups/{$this->smallGroup1->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Prayer Group')
            ->assertJsonPath('data.description', 'Updated description');

        $this->assertDatabaseHas('small_groups', [
            'id' => $this->smallGroup1->id,
            'name' => 'Updated Prayer Group',
            'meeting_day' => 'Sunday',
        ]);
    }

    /** @test */
    public function branch_pastor_cannot_change_branch_of_small_group(): void
    {
        $this->actingAs($this->branchPastor1, 'sanctum');

        $updateData = [
            'name' => 'Updated Group',
            'branch_id' => $this->branch2->id, // Trying to change branch
        ];

        $response = $this->putJson("/api/small-groups/{$this->smallGroup1->id}", $updateData);

        $response->assertOk();

        // Branch should remain unchanged
        $this->assertDatabaseHas('small_groups', [
            'id' => $this->smallGroup1->id,
            'branch_id' => $this->branch1->id, // Original branch
        ]);
    }

    /** @test */
    public function can_delete_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->deleteJson("/api/small-groups/{$this->smallGroup1->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('small_groups', [
            'id' => $this->smallGroup1->id,
        ]);

        // Check that member associations are removed
        $this->assertDatabaseEmpty('member_small_groups');
    }

    /** @test */
    public function can_assign_members_to_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        // Create a new member in the same branch
        $newMember = Member::factory()->create(['branch_id' => $this->branch1->id]);

        $response = $this->postJson("/api/small-groups/{$this->smallGroup1->id}/assign-members", [
            'member_ids' => [$newMember->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('assigned_count', 1);

        $this->assertDatabaseHas('member_small_groups', [
            'small_group_id' => $this->smallGroup1->id,
            'member_id' => $newMember->id,
        ]);
    }

    /** @test */
    public function cannot_assign_members_from_different_branch(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->postJson("/api/small-groups/{$this->smallGroup1->id}/assign-members", [
            'member_ids' => [$this->member3->id], // Member from different branch
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function can_remove_members_from_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->deleteJson("/api/small-groups/{$this->smallGroup1->id}/remove-members", [
            'member_ids' => [$this->member2->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('removed_count', 1);

        $this->assertDatabaseMissing('member_small_groups', [
            'small_group_id' => $this->smallGroup1->id,
            'member_id' => $this->member2->id,
        ]);
    }

    /** @test */
    public function can_change_small_group_leader(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->putJson("/api/small-groups/{$this->smallGroup1->id}/change-leader", [
            'leader_id' => $this->member2->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('small_groups', [
            'id' => $this->smallGroup1->id,
            'leader_id' => $this->member2->id,
        ]);
    }

    /** @test */
    public function can_remove_small_group_leader(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->putJson("/api/small-groups/{$this->smallGroup1->id}/change-leader", [
            'leader_id' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('small_groups', [
            'id' => $this->smallGroup1->id,
            'leader_id' => null,
        ]);
    }

    /** @test */
    public function cannot_set_leader_from_different_branch(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->putJson("/api/small-groups/{$this->smallGroup1->id}/change-leader", [
            'leader_id' => $this->member3->id, // Member from different branch
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function can_get_available_members_for_small_group(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        // Create additional members in the same branch
        $availableMember = Member::factory()->create(['branch_id' => $this->branch1->id]);

        $response = $this->getJson("/api/small-groups/{$this->smallGroup1->id}/available-members");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'phone', 'email', 'member_status']
                ],
                'message',
            ]);

        // Should include the available member but not the ones already in the group
        $memberIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($availableMember->id, $memberIds);
        $this->assertNotContains($this->member1->id, $memberIds); // Already in group
        $this->assertNotContains($this->member2->id, $memberIds); // Already in group
    }

    /** @test */
    public function super_admin_can_get_small_group_statistics(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson('/api/small-groups/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_groups',
                    'active_groups',
                    'inactive_groups',
                    'groups_with_leaders',
                    'groups_without_leader',
                    'total_members',
                    'average_group_size',
                ],
                'message',
            ])
            ->assertJsonPath('data.total_groups', 2)
            ->assertJsonPath('data.active_groups', 2)
            ->assertJsonPath('data.groups_with_leaders', 1)
            ->assertJsonPath('data.groups_without_leader', 1);
    }

    /** @test */
    public function branch_pastor_gets_filtered_statistics(): void
    {
        $this->actingAs($this->branchPastor1, 'sanctum');

        $response = $this->getJson('/api/small-groups/statistics');

        $response->assertOk()
            ->assertJsonPath('data.total_groups', 1) // Only their branch
            ->assertJsonPath('data.groups_with_leaders', 1);
    }

    /** @test */
    public function can_filter_statistics_by_branch(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->getJson("/api/small-groups/statistics?branch_id={$this->branch1->id}");

        $response->assertOk()
            ->assertJsonPath('data.total_groups', 1)
            ->assertJsonPath('data.groups_with_leaders', 1);
    }

    /** @test */
    public function small_group_validation_works(): void
    {
        $this->actingAs($this->superAdmin, 'sanctum');

        $response = $this->postJson('/api/small-groups', [
            'name' => '', // Required
            'branch_id' => 999, // Invalid
            'leader_id' => 999, // Invalid
            'meeting_day' => 'InvalidDay', // Invalid
            'meeting_time' => '25:00', // Invalid format
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'branch_id', 'leader_id', 'meeting_day', 'meeting_time']);
    }

    /** @test */
    public function regular_user_cannot_create_small_groups(): void
    {
        $this->actingAs($this->regularUser, 'sanctum');

        $response = $this->postJson('/api/small-groups', [
            'name' => 'Test Group',
            'branch_id' => $this->branch1->id,
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function regular_user_cannot_delete_small_groups(): void
    {
        $this->actingAs($this->regularUser, 'sanctum');

        $response = $this->deleteJson("/api/small-groups/{$this->smallGroup1->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function small_group_leader_can_update_their_own_group(): void
    {
        // Set up a user who is a small group leader
        $leaderUser = User::factory()->create();
        $leaderMember = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'user_id' => $leaderUser->id,
        ]);
        
        $leaderGroup = SmallGroup::factory()->create([
            'branch_id' => $this->branch1->id,
            'leader_id' => $leaderMember->id,
        ]);

        $this->actingAs($leaderUser, 'sanctum');

        $response = $this->putJson("/api/small-groups/{$leaderGroup->id}", [
            'name' => 'Updated by Leader',
            'description' => 'Updated description',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated by Leader');
    }

    /** @test */
    public function small_group_leader_can_manage_their_group_members(): void
    {
        // Set up a user who is a small group leader
        $leaderUser = User::factory()->create();
        $leaderMember = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'user_id' => $leaderUser->id,
        ]);
        
        $leaderGroup = SmallGroup::factory()->create([
            'branch_id' => $this->branch1->id,
            'leader_id' => $leaderMember->id,
        ]);

        $newMember = Member::factory()->create(['branch_id' => $this->branch1->id]);

        $this->actingAs($leaderUser, 'sanctum');

        $response = $this->postJson("/api/small-groups/{$leaderGroup->id}/assign-members", [
            'member_ids' => [$newMember->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }
}
