<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberStatusHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class MemberStatusManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $superAdmin;
    private User $branchPastor;
    private User $regularUser;
    private Branch $branch1;
    private Branch $branch2;
    private Member $member1;
    private Member $member2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::factory()->create(['name' => 'super_admin']);
        $branchPastorRole = Role::factory()->create(['name' => 'branch_pastor']);
        $memberRole = Role::factory()->create(['name' => 'member']);

        // Create branches
        $this->branch1 = Branch::factory()->create(['name' => 'Main Branch']);
        $this->branch2 = Branch::factory()->create(['name' => 'Second Branch']);

        // Create users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->roles()->attach($superAdminRole->id);

        $this->branchPastor = User::factory()->create();
        $this->branchPastor->roles()->attach($branchPastorRole->id, ['branch_id' => $this->branch1->id]);

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach($memberRole->id);

        // Create members
        $this->member1 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'member_status' => 'member',
        ]);

        $this->member2 = Member::factory()->create([
            'branch_id' => $this->branch2->id,
            'member_status' => 'volunteer',
        ]);
    }

    /** @test */
    public function super_admin_can_change_member_status(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}/change-status", [
                'status' => 'leader',
                'reason' => 'Promoted due to leadership qualities',
                'notes' => 'Member has shown excellent leadership skills.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member status updated successfully.',
            ]);

        // Verify member status was updated
        $this->member1->refresh();
        $this->assertEquals('leader', $this->member1->member_status);

        // Verify status history was created
        $this->assertDatabaseHas('member_status_histories', [
            'member_id' => $this->member1->id,
            'changed_by' => $this->superAdmin->id,
            'previous_status' => 'member',
            'new_status' => 'leader',
            'reason' => 'Promoted due to leadership qualities',
            'notes' => 'Member has shown excellent leadership skills.',
        ]);
    }

    /** @test */
    public function branch_pastor_can_change_status_of_their_branch_members(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->putJson("/api/members/{$this->member1->id}/change-status", [
                'status' => 'volunteer',
                'reason' => 'Started volunteering in ministry',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member status updated successfully.',
            ]);

        $this->member1->refresh();
        $this->assertEquals('volunteer', $this->member1->member_status);
    }

    /** @test */
    public function branch_pastor_cannot_change_status_of_other_branch_members(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->putJson("/api/members/{$this->member2->id}/change-status", [
                'status' => 'leader',
                'reason' => 'Promotion',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_change_member_status(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/members/{$this->member1->id}/change-status", [
                'status' => 'leader',
                'reason' => 'Promotion',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_change_to_same_status(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}/change-status", [
                'status' => 'member', // Same as current status
                'reason' => 'No change needed',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Member is already in the requested status.',
            ]);
    }

    /** @test */
    public function status_change_validation_works(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}/change-status", [
                'status' => 'invalid_status',
                'reason' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function can_get_member_status_history(): void
    {
        // Create some status history
        MemberStatusHistory::factory()->count(3)->create([
            'member_id' => $this->member1->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members/{$this->member1->id}/status-history");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'member_id',
                            'changed_by',
                            'previous_status',
                            'new_status',
                            'reason',
                            'notes',
                            'changed_at',
                            'changed_by' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function branch_pastor_cannot_view_other_branch_member_history(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/members/{$this->member2->id}/status-history");

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_get_status_statistics(): void
    {
        // Create members with different statuses
        Member::factory()->create(['member_status' => 'visitor', 'branch_id' => $this->branch1->id]);
        Member::factory()->create(['member_status' => 'volunteer', 'branch_id' => $this->branch1->id]);
        Member::factory()->create(['member_status' => 'leader', 'branch_id' => $this->branch2->id]);

        // Create some recent status changes
        MemberStatusHistory::factory()->recent()->count(2)->create();

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members/status/statistics');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status_counts',
                    'recent_changes' => [
                        '*' => [
                            'id',
                            'member_id',
                            'previous_status',
                            'new_status',
                            'reason',
                            'changed_at',
                            'member' => [
                                'id',
                                'name',
                                'branch' => [
                                    'id',
                                    'name',
                                ],
                            ],
                            'changed_by' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                    'branch_distribution',
                    'total_members',
                ],
            ]);
    }

    /** @test */
    public function branch_pastor_gets_filtered_status_statistics(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson('/api/members/status/statistics');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify that branch distribution is empty (not available for branch pastors)
        $data = $response->json('data');
        $this->assertEmpty($data['branch_distribution']);
    }

    /** @test */
    public function can_filter_status_statistics_by_branch(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members/status/statistics?branch_id={$this->branch1->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // When filtered by branch, branch_distribution should be empty
        $data = $response->json('data');
        $this->assertEmpty($data['branch_distribution']);
    }

    /** @test */
    public function super_admin_can_bulk_update_member_statuses(): void
    {
        $member3 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'member_status' => 'visitor',
        ]);

        $memberIds = [$this->member1->id, $member3->id];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/members/bulk-update-status', [
                'member_ids' => $memberIds,
                'status' => 'volunteer',
                'reason' => 'Bulk promotion to volunteer status',
                'notes' => 'Mass update for active participants',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'updated_count' => 2,
                    'total_requested' => 2,
                ],
            ]);

        // Verify members were updated
        $this->member1->refresh();
        $member3->refresh();
        $this->assertEquals('volunteer', $this->member1->member_status);
        $this->assertEquals('volunteer', $member3->member_status);

        // Verify status history was created for both
        $this->assertDatabaseHas('member_status_histories', [
            'member_id' => $this->member1->id,
            'new_status' => 'volunteer',
            'reason' => 'Bulk promotion to volunteer status',
        ]);

        $this->assertDatabaseHas('member_status_histories', [
            'member_id' => $member3->id,
            'new_status' => 'volunteer',
            'reason' => 'Bulk promotion to volunteer status',
        ]);
    }

    /** @test */
    public function bulk_update_respects_authorization(): void
    {
        $memberIds = [$this->member1->id, $this->member2->id]; // member2 is in different branch

        $response = $this->actingAs($this->branchPastor)
            ->postJson('/api/members/bulk-update-status', [
                'member_ids' => $memberIds,
                'status' => 'volunteer',
                'reason' => 'Bulk update',
            ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(1, $data['updated_count']); // Only member1 should be updated
        $this->assertEquals(2, $data['total_requested']);
        $this->assertCount(1, $data['errors']); // Should have error for member2
    }

    /** @test */
    public function bulk_update_validation_works(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/members/bulk-update-status', [
                'member_ids' => [],
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['member_ids', 'status']);
    }

    /** @test */
    public function bulk_update_handles_non_existent_members(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/members/bulk-update-status', [
                'member_ids' => [99999], // Non-existent member ID
                'status' => 'volunteer',
                'reason' => 'Test',
            ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(0, $data['updated_count']);
        $this->assertEquals(1, $data['total_requested']);
        $this->assertCount(1, $data['errors']);
    }

    /** @test */
    public function automatic_status_tracking_works_with_role_assignments(): void
    {
        // Test that automatic status changes are tracked in history
        $this->member1->updateStatusBasedOnAssignments();

        // Since member1 has no assignments, status should remain 'member'
        // and no history should be created (no change)
        $this->assertEquals('member', $this->member1->member_status);
        $this->assertDatabaseMissing('member_status_histories', [
            'member_id' => $this->member1->id,
        ]);

        // Now simulate assignment by changing status to leader
        $this->member1->member_status = 'leader';
        $this->member1->save();

        // Then call updateStatusBasedOnAssignments (simulating removal of assignment)
        $this->member1->updateStatusBasedOnAssignments();

        // Status should change back to 'member' and history should be created
        $this->assertEquals('member', $this->member1->member_status);
        $this->assertDatabaseHas('member_status_histories', [
            'member_id' => $this->member1->id,
            'previous_status' => 'leader',
            'new_status' => 'member',
            'reason' => 'Automatic status update based on role assignments',
        ]);
    }

    /** @test */
    public function member_change_status_method_works_correctly(): void
    {
        $changed = $this->member1->changeStatus(
            'volunteer',
            'Manual status change',
            'Updated via API',
            $this->superAdmin->id
        );

        $this->assertTrue($changed);
        $this->assertEquals('volunteer', $this->member1->member_status);

        // Verify history was logged
        $this->assertDatabaseHas('member_status_histories', [
            'member_id' => $this->member1->id,
            'changed_by' => $this->superAdmin->id,
            'previous_status' => 'member',
            'new_status' => 'volunteer',
            'reason' => 'Manual status change',
            'notes' => 'Updated via API',
        ]);
    }

    /** @test */
    public function member_change_status_returns_false_for_same_status(): void
    {
        $changed = $this->member1->changeStatus(
            'member', // Same as current status
            'No change',
            'Test',
            $this->superAdmin->id
        );

        $this->assertFalse($changed);

        // No history should be created
        $this->assertDatabaseMissing('member_status_histories', [
            'member_id' => $this->member1->id,
        ]);
    }
}
