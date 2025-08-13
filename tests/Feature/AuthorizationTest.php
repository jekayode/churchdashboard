<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

final class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed the database with roles and other data
    }

    public function test_super_admin_can_access_everything(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();
        
        // Create a super admin user
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id, ['branch_id' => $branch->id]);

        // Create an event
        $event = Event::factory()->create(['branch_id' => $branch->id]);

        // Test that super admin can view any event
        $this->assertTrue(Gate::forUser($superAdmin)->allows('view', $event));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', $event));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', $event));
    }

    public function test_branch_pastor_can_manage_branch_resources(): void
    {
        // Create branches
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        
        // Create a branch pastor user
        $branchPastor = User::factory()->create();
        $branchPastorRole = Role::where('name', 'branch_pastor')->first();
        $branchPastor->roles()->attach($branchPastorRole->id, ['branch_id' => $branch1->id]);

        // Create events in different branches
        $event1 = Event::factory()->create(['branch_id' => $branch1->id]);
        $event2 = Event::factory()->create(['branch_id' => $branch2->id]);

        // Test that branch pastor can manage events in their branch
        $this->assertTrue(Gate::forUser($branchPastor)->allows('view', $event1));
        $this->assertTrue(Gate::forUser($branchPastor)->allows('update', $event1));

        // Test that branch pastor cannot manage events in other branches
        $this->assertFalse(Gate::forUser($branchPastor)->allows('view', $event2));
        $this->assertFalse(Gate::forUser($branchPastor)->allows('update', $event2));
    }

    public function test_church_member_has_limited_access(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();
        
        // Create a church member user
        $churchMember = User::factory()->create();
        $churchMemberRole = Role::where('name', 'church_member')->first();
        $churchMember->roles()->attach($churchMemberRole->id, ['branch_id' => $branch->id]);

        // Create events in the same branch
        $event1 = Event::factory()->create(['branch_id' => $branch->id]);
        $event2 = Event::factory()->create(['branch_id' => $branch->id]);

        // Test that church member can view events in their branch
        $this->assertTrue(Gate::forUser($churchMember)->allows('view', $event1));
        $this->assertTrue(Gate::forUser($churchMember)->allows('view', $event2));

        // Test that church member cannot create events
        $this->assertFalse(Gate::forUser($churchMember)->allows('create', Event::class));

        // Test that church member cannot update events
        $this->assertFalse(Gate::forUser($churchMember)->allows('update', $event1));
    }

    public function test_user_can_manage_own_profile(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();
        
        // Create users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Assign them to the branch with church member role
        $churchMemberRole = Role::where('name', 'church_member')->first();
        $user1->roles()->attach($churchMemberRole->id, ['branch_id' => $branch->id]);
        $user2->roles()->attach($churchMemberRole->id, ['branch_id' => $branch->id]);

        // Test that user can view their own profile
        $this->assertTrue(Gate::forUser($user1)->allows('view', $user1));

        // Test that user can update their own profile
        $this->assertTrue(Gate::forUser($user1)->allows('update', $user1));

        // Test that user cannot view other user's profile (unless they have leadership privileges)
        $this->assertFalse(Gate::forUser($user1)->allows('view', $user2));

        // Test that user cannot update other user's profile
        $this->assertFalse(Gate::forUser($user1)->allows('update', $user2));
    }

    public function test_branch_scope_restrictions(): void
    {
        // Create branches
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        
        // Create users in different branches
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $ministryLeaderRole = Role::where('name', 'ministry_leader')->first();
        $user1->roles()->attach($ministryLeaderRole->id, ['branch_id' => $branch1->id]);
        $user2->roles()->attach($ministryLeaderRole->id, ['branch_id' => $branch2->id]);

        // Create events in different branches
        $event1 = Event::factory()->create(['branch_id' => $branch1->id]);
        $event2 = Event::factory()->create(['branch_id' => $branch2->id]);

        // Test that users can only access resources in their branch
        $this->assertTrue(Gate::forUser($user1)->allows('view', $event1));
        $this->assertFalse(Gate::forUser($user1)->allows('view', $event2));
        
        $this->assertTrue(Gate::forUser($user2)->allows('view', $event2));
        $this->assertFalse(Gate::forUser($user2)->allows('view', $event1));
    }
} 