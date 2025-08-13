<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\SmallGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class MemberControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $superAdmin;
    private User $branchPastor;
    private Branch $branch1;
    private Branch $branch2;
    private Member $member1;
    private Member $member2;
    private Member $member3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin', 'display_name' => 'Super Admin']);
        Role::create(['name' => 'branch_pastor', 'display_name' => 'Branch Pastor']);

        // Create branches
        $this->branch1 = Branch::factory()->create(['name' => 'Main Branch']);
        $this->branch2 = Branch::factory()->create(['name' => 'Second Branch']);

        // Create users
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->branchPastor = User::factory()->create();
        $this->branchPastor->assignRole('branch_pastor', $this->branch1->id);

        // Create members
        $this->member1 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'member_status' => 'member',
            'gender' => 'male',
            'growth_level' => 'growing',
            'teci_status' => '200_level',
        ]);

        $this->member2 = Member::factory()->create([
            'branch_id' => $this->branch1->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+0987654321',
            'member_status' => 'volunteer',
            'gender' => 'female',
            'growth_level' => 'core',
            'teci_status' => '300_level',
        ]);

        $this->member3 = Member::factory()->create([
            'branch_id' => $this->branch2->id,
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'member_status' => 'leader',
            'gender' => 'male',
            'growth_level' => 'pastor',
            'teci_status' => 'graduated',
        ]);
    }

    public function test_super_admin_can_list_all_members(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'member_status',
                            'branch',
                            'age',
                            'is_leader',
                            'is_volunteer',
                            'leadership_roles'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ],
                'message',
                'filters'
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_branch_pastor_can_only_see_own_branch_members(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson('/api/members');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data'); // Only branch1 members

        $memberIds = collect($response->json('data.data'))->pluck('id')->toArray();
        $this->assertContains($this->member1->id, $memberIds);
        $this->assertContains($this->member2->id, $memberIds);
        $this->assertNotContains($this->member3->id, $memberIds);
    }

    public function test_can_search_members_by_name(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?search=John');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data'); // John Doe and Bob Johnson
    }

    public function test_can_search_members_by_email(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?search=jane@example.com');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Jane Smith');
    }

    public function test_can_search_members_by_phone(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?search=+1234567890');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'John Doe');
    }

    public function test_can_filter_members_by_status(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?member_status=volunteer');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Jane Smith');
    }

    public function test_can_filter_members_by_gender(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?gender=female');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Jane Smith');
    }

    public function test_can_filter_members_by_growth_level(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?growth_level=core');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Jane Smith');
    }

    public function test_can_filter_members_by_teci_status(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?teci_status=graduated');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Bob Johnson');
    }

    public function test_super_admin_can_filter_by_branch(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members?branch_id={$this->branch2->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Bob Johnson');
    }

    public function test_branch_pastor_cannot_filter_by_other_branches(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/members?branch_id={$this->branch2->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data'); // Still gets own branch members
    }

    public function test_can_sort_members_by_name(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?sort_by=name&sort_direction=desc');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $names = collect($response->json('data.data'))->pluck('name')->toArray();
        $this->assertEquals(['John Doe', 'Jane Smith', 'Bob Johnson'], $names);
    }

    public function test_can_paginate_members(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?per_page=2');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.per_page', 2)
            ->assertJsonPath('data.total', 3);
    }

    public function test_can_create_member(): void
    {
        $memberData = [
            'branch_id' => $this->branch1->id,
            'name' => 'New Member',
            'email' => 'new@example.com',
            'phone' => '+1111111111',
            'gender' => 'male',
            'member_status' => 'member',
            'growth_level' => 'new_believer',
            'teci_status' => 'not_started',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/members', $memberData);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Member')
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('members', [
            'name' => 'New Member',
            'email' => 'new@example.com',
            'branch_id' => $this->branch1->id,
        ]);
    }

    public function test_cannot_create_member_with_invalid_data(): void
    {
        $memberData = [
            'name' => '', // Required field empty
            'email' => 'invalid-email', // Invalid email format
            'branch_id' => 999, // Non-existent branch
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/members', $memberData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'branch_id']);
    }

    public function test_can_show_member_details(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members/{$this->member1->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->member1->id)
            ->assertJsonPath('data.name', $this->member1->name)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'branch',
                    'user',
                    'departments',
                    'small_groups',
                    'led_ministries',
                    'led_departments',
                    'led_small_groups',
                    'age',
                    'is_leader',
                    'is_volunteer',
                    'leadership_roles'
                ]
            ]);
    }

    public function test_branch_pastor_cannot_view_other_branch_member(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson("/api/members/{$this->member3->id}");

        $response->assertForbidden();
    }

    public function test_can_update_member(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'branch_id' => $this->member1->branch_id,
            'member_status' => 'volunteer',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('members', [
            'id' => $this->member1->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_can_delete_member_without_leadership_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/members/{$this->member1->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('members', ['id' => $this->member1->id]);
    }

    public function test_cannot_delete_member_with_leadership_roles(): void
    {
        // Create a ministry and assign member as leader
        $ministry = Ministry::factory()->create([
            'branch_id' => $this->branch1->id,
            'leader_id' => $this->member1->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/members/{$this->member1->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Cannot delete member with active leadership roles.');

        $this->assertDatabaseHas('members', ['id' => $this->member1->id]);
    }

    public function test_can_assign_member_to_departments(): void
    {
        $ministry = Ministry::factory()->create(['branch_id' => $this->branch1->id]);
        $department1 = Department::factory()->create(['ministry_id' => $ministry->id]);
        $department2 = Department::factory()->create(['ministry_id' => $ministry->id]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/members/{$this->member1->id}/assign-departments", [
                'department_ids' => [$department1->id, $department2->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('member_departments', [
            'member_id' => $this->member1->id,
            'department_id' => $department1->id,
        ]);

        $this->assertDatabaseHas('member_departments', [
            'member_id' => $this->member1->id,
            'department_id' => $department2->id,
        ]);
    }

    public function test_can_assign_member_to_small_groups(): void
    {
        $smallGroup1 = SmallGroup::factory()->create(['branch_id' => $this->branch1->id]);
        $smallGroup2 = SmallGroup::factory()->create(['branch_id' => $this->branch1->id]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/members/{$this->member1->id}/assign-small-groups", [
                'small_group_ids' => [$smallGroup1->id, $smallGroup2->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('member_small_groups', [
            'member_id' => $this->member1->id,
            'small_group_id' => $smallGroup1->id,
        ]);

        $this->assertDatabaseHas('member_small_groups', [
            'member_id' => $this->member1->id,
            'small_group_id' => $smallGroup2->id,
        ]);
    }

    public function test_can_update_member_growth_level(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}/growth-level", [
                'growth_level' => 'core',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('members', [
            'id' => $this->member1->id,
            'growth_level' => 'core',
        ]);
    }

    public function test_can_update_member_teci_progress(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/members/{$this->member1->id}/teci-progress", [
                'teci_status' => '400_level',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('members', [
            'id' => $this->member1->id,
            'teci_status' => '400_level',
        ]);
    }

    public function test_can_filter_members_by_ministry_involvement(): void
    {
        $ministry = Ministry::factory()->create(['branch_id' => $this->branch1->id]);
        $department = Department::factory()->create(['ministry_id' => $ministry->id]);
        
        // Assign member to department
        $this->member1->departments()->attach($department->id, ['assigned_at' => now()]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members?ministry_id={$ministry->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $this->member1->id);
    }

    public function test_can_filter_members_by_department_involvement(): void
    {
        $ministry = Ministry::factory()->create(['branch_id' => $this->branch1->id]);
        $department = Department::factory()->create(['ministry_id' => $ministry->id]);
        
        // Assign member to department
        $this->member1->departments()->attach($department->id, ['assigned_at' => now()]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members?department_id={$department->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $this->member1->id);
    }

    public function test_can_filter_members_by_small_group_involvement(): void
    {
        $smallGroup = SmallGroup::factory()->create(['branch_id' => $this->branch1->id]);
        
        // Assign member to small group
        $this->member1->smallGroups()->attach($smallGroup->id, ['joined_at' => now()]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/members?small_group_id={$smallGroup->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $this->member1->id);
    }

    public function test_can_filter_members_by_leadership_role(): void
    {
        // Create a ministry and assign member as leader
        $ministry = Ministry::factory()->create([
            'branch_id' => $this->branch1->id,
            'leader_id' => $this->member1->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?has_leadership_role=true');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $this->member1->id);

        // Test filtering for non-leaders
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members?has_leadership_role=false');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data'); // member2 and member3
    }

    public function test_unauthorized_user_cannot_access_members(): void
    {
        $response = $this->getJson('/api/members');

        $response->assertUnauthorized();
    }

    public function test_returns_available_filters_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/members');

        $response->assertOk()
            ->assertJsonStructure([
                'filters' => [
                    'member_status',
                    'gender',
                    'marital_status',
                    'growth_level',
                    'teci_status',
                    'branches'
                ]
            ]);
    }

    public function test_returns_limited_filters_for_branch_pastor(): void
    {
        $response = $this->actingAs($this->branchPastor)
            ->getJson('/api/members');

        $response->assertOk()
            ->assertJsonStructure([
                'filters' => [
                    'member_status',
                    'gender',
                    'marital_status',
                    'growth_level',
                    'teci_status'
                ]
            ])
            ->assertJsonMissing(['filters' => ['branches']]);
    }
} 