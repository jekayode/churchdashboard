<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

final class MiddlewareAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed the database with roles and other data
    }

    public function test_branch_scope_middleware_restricts_access(): void
    {
        // Create branches
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        
        // Create users in different branches
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $churchMemberRole = Role::where('name', 'church_member')->first();
        $user1->roles()->attach($churchMemberRole->id, ['branch_id' => $branch1->id]);
        $user2->roles()->attach($churchMemberRole->id, ['branch_id' => $branch2->id]);

        // Test that user1 can access their branch context
        $this->actingAs($user1);
        
        // Create a test route with branch scope middleware
        Route::get('/test-branch-scope', function () {
            return response()->json(['message' => 'Access granted']);
        })->middleware(['auth', 'branch.scope']);

        $response = $this->get('/test-branch-scope');
        $response->assertStatus(200);
    }

    public function test_authorization_middleware_protects_resources(): void
    {
        // Create a branch
        $branch = Branch::factory()->create();
        
        // Create users with different roles
        $superAdmin = User::factory()->create();
        $churchMember = User::factory()->create();
        
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $churchMemberRole = Role::where('name', 'church_member')->first();
        
        $superAdmin->roles()->attach($superAdminRole->id, ['branch_id' => $branch->id]);
        $churchMember->roles()->attach($churchMemberRole->id, ['branch_id' => $branch->id]);

        // Create an event
        $event = Event::factory()->create(['branch_id' => $branch->id]);

        // Test that super admin can access event management
        $this->actingAs($superAdmin);
        
        // Create a test route with authorization middleware - use explicit model binding
        Route::bind('event', function ($value) {
            return Event::findOrFail($value);
        });
        
        Route::get('/test-event-access/{event}', function (Event $event) {
            return response()->json(['message' => 'Access granted', 'event' => $event->name]);
        })->middleware(['auth', 'authorize.resource:view,Event']);

        $response = $this->get("/test-event-access/{$event->id}");
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Access granted']);

        // Test that church member can also view events (but not manage them)
        $this->actingAs($churchMember);
        
        $response = $this->get("/test-event-access/{$event->id}");
        $response->assertStatus(200);
    }

    public function test_unauthorized_access_is_blocked(): void
    {
        // Create branches
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        
        // Create a user in branch1
        $user = User::factory()->create();
        $churchMemberRole = Role::where('name', 'church_member')->first();
        $user->roles()->attach($churchMemberRole->id, ['branch_id' => $branch1->id]);

        // Create an event in branch2
        $event = Event::factory()->create(['branch_id' => $branch2->id]);

        $this->actingAs($user);
        
        // Create a test route that should be blocked
        Route::get('/test-unauthorized/{event}', function (Event $event) {
            return response()->json(['message' => 'Access granted']);
        })->middleware(['auth', 'authorize.resource:view,Event']);

        // This should fail because user is not in the same branch as the event
        $response = $this->get("/test-unauthorized/{$event->id}");
        $response->assertStatus(403);
    }
} 