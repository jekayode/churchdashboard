<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Member;
use App\Models\SmallGroup;
use App\Models\SmallGroupJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class SmallGroupJoinRequestTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $leaderUser;

    private Member $leaderMember;

    private SmallGroup $group;

    private Member $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();

        // Group leader
        $this->leaderUser = User::factory()->create();
        $this->leaderUser->assignRole('church_member', $this->branch->id);
        $this->leaderMember = Member::factory()->create([
            'user_id' => $this->leaderUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->group = SmallGroup::factory()->create([
            'branch_id' => $this->branch->id,
            'status' => 'active',
            'leader_id' => $this->leaderMember->id,
        ]);

        $this->applicant = Member::factory()->create(['branch_id' => $this->branch->id]);
    }

    private function pendingRequest(): SmallGroupJoinRequest
    {
        return SmallGroupJoinRequest::create([
            'small_group_id' => $this->group->id,
            'member_id' => $this->applicant->id,
            'status' => 'pending',
        ]);
    }

    public function test_guest_cannot_list_requests(): void
    {
        $this->getJson('/api/small-group-join-requests')->assertUnauthorized();
    }

    public function test_leader_sees_pending_requests_for_their_group(): void
    {
        $this->pendingRequest();
        Sanctum::actingAs($this->leaderUser);

        $response = $this->getJson('/api/small-group-join-requests')->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame($this->applicant->id, $response->json('data.0.member.id'));
    }

    public function test_unrelated_member_sees_no_requests(): void
    {
        $this->pendingRequest();

        $stranger = User::factory()->create();
        $stranger->assignRole('church_member', $this->branch->id);
        Member::factory()->create(['user_id' => $stranger->id, 'branch_id' => $this->branch->id]);

        Sanctum::actingAs($stranger);

        $this->getJson('/api/small-group-join-requests')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_leader_can_approve_and_member_is_added_to_group(): void
    {
        $joinRequest = $this->pendingRequest();
        Sanctum::actingAs($this->leaderUser);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve", [
            'response_note' => 'Welcome!',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('small_group_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'approved',
            'reviewed_by' => $this->leaderUser->id,
        ]);

        $this->assertDatabaseHas('member_small_groups', [
            'small_group_id' => $this->group->id,
            'member_id' => $this->applicant->id,
        ]);
    }

    public function test_leader_can_decline_without_adding_member(): void
    {
        $joinRequest = $this->pendingRequest();
        Sanctum::actingAs($this->leaderUser);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/decline")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('small_group_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'declined',
        ]);

        $this->assertDatabaseMissing('member_small_groups', [
            'small_group_id' => $this->group->id,
            'member_id' => $this->applicant->id,
        ]);
    }

    public function test_cannot_review_twice(): void
    {
        $joinRequest = $this->pendingRequest();
        Sanctum::actingAs($this->leaderUser);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve")->assertOk();
        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve")->assertUnprocessable();
    }

    public function test_member_cannot_approve_their_own_request(): void
    {
        $applicantUser = User::factory()->create();
        $applicantUser->assignRole('church_member', $this->branch->id);
        $this->applicant->update(['user_id' => $applicantUser->id]);

        $joinRequest = $this->pendingRequest();
        Sanctum::actingAs($applicantUser);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve")->assertForbidden();

        $this->assertDatabaseHas('small_group_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_branch_pastor_can_review_requests_in_their_branch(): void
    {
        $joinRequest = $this->pendingRequest();

        $pastor = User::factory()->create();
        $pastor->assignRole('branch_pastor', $this->branch->id);
        Sanctum::actingAs($pastor);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve")->assertOk();
    }

    public function test_pastor_of_another_branch_cannot_review(): void
    {
        $joinRequest = $this->pendingRequest();

        $otherBranch = Branch::factory()->create();
        $otherPastor = User::factory()->create();
        $otherPastor->assignRole('branch_pastor', $otherBranch->id);
        Sanctum::actingAs($otherPastor);

        $this->postJson("/api/small-group-join-requests/{$joinRequest->id}/approve")->assertForbidden();
    }
}
