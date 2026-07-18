<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Member;
use App\Models\SmallGroup;
use App\Models\SmallGroupJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeSmallGroupsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Member $member;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->branch = Branch::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('church_member', $this->branch->id);
        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    private function group(array $attributes = []): SmallGroup
    {
        return SmallGroup::factory()->create(array_merge([
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ], $attributes));
    }

    public function test_guest_cannot_access_small_groups(): void
    {
        $this->getJson('/api/me/small-groups')->assertUnauthorized();
    }

    public function test_lists_my_groups_with_members(): void
    {
        $group = $this->group(['name' => 'Tuesday Group']);
        $group->members()->attach($this->member->id, ['joined_at' => now()]);
        $this->group(['name' => 'Not Mine']);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/small-groups')->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Tuesday Group', $response->json('data.0.name'));
        $this->assertSame(1, $response->json('data.0.members_count'));
    }

    public function test_available_excludes_groups_i_am_in_and_other_branches(): void
    {
        $joined = $this->group(['name' => 'Already In']);
        $joined->members()->attach($this->member->id, ['joined_at' => now()]);

        $this->group(['name' => 'Open Group']);
        $this->group(['name' => 'Inactive Group', 'status' => 'inactive']);

        $otherBranch = Branch::factory()->create();
        SmallGroup::factory()->create(['branch_id' => $otherBranch->id, 'status' => 'active', 'name' => 'Other Branch Group']);

        Sanctum::actingAs($this->user);

        $names = collect($this->getJson('/api/me/small-groups/available')->assertOk()->json('data'))->pluck('name');

        $this->assertContains('Open Group', $names);
        $this->assertNotContains('Already In', $names);
        $this->assertNotContains('Inactive Group', $names);
        $this->assertNotContains('Other Branch Group', $names);
    }

    public function test_member_can_request_to_join(): void
    {
        $group = $this->group();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/small-groups/{$group->id}/join-request", [
            'message' => 'I would like to join.',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('small_group_join_requests', [
            'small_group_id' => $group->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_request_twice_while_pending(): void
    {
        $group = $this->group();
        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/small-groups/{$group->id}/join-request")->assertCreated();
        $this->postJson("/api/me/small-groups/{$group->id}/join-request")->assertStatus(409);
    }

    public function test_cannot_request_to_join_other_branch_group(): void
    {
        $otherBranch = Branch::factory()->create();
        $group = SmallGroup::factory()->create(['branch_id' => $otherBranch->id, 'status' => 'active']);

        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/small-groups/{$group->id}/join-request")->assertForbidden();
    }

    public function test_cannot_request_to_join_group_i_am_already_in(): void
    {
        $group = $this->group();
        $group->members()->attach($this->member->id, ['joined_at' => now()]);

        Sanctum::actingAs($this->user);

        $this->postJson("/api/me/small-groups/{$group->id}/join-request")->assertStatus(409);
    }

    public function test_member_can_see_own_join_requests(): void
    {
        $group = $this->group(['name' => 'Requested Group']);
        SmallGroupJoinRequest::create([
            'small_group_id' => $group->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        // Another member's request must not leak.
        SmallGroupJoinRequest::create([
            'small_group_id' => $group->id,
            'member_id' => Member::factory()->create(['branch_id' => $this->branch->id])->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/me/small-groups/join-requests')->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Requested Group', $response->json('data.0.small_group.name'));
    }
}
