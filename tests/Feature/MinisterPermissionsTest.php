<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\SmallGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MinisterPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeBranchWithMinister(string $category): array
    {
        $branch = Branch::factory()->create();

        $user = User::factory()->create();
        // Give user ministry_leader role on this branch
        $user->assignRole('ministry_leader', $branch->id);

        // Create member profile for user (needed for leader foreign key checks)
        $member = Member::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);

        // Ministry led by this member
        $ministry = Ministry::factory()->create([
            'branch_id' => $branch->id,
            'leader_id' => $member->id,
            'category' => $category,
        ]);

        return [$branch, $user, $member, $ministry];
    }

    public function test_operations_minister_can_create_update_delete_events(): void
    {
        [$branch, $user] = $this->makeBranchWithMinister('operations');
        $this->actingAs($user);

        // Create event (policy create)
        $event = Event::factory()->create(['branch_id' => $branch->id]);
        $this->assertTrue($user->can('create', Event::class));
        $this->assertTrue($user->can('update', $event));
        $this->assertTrue($user->can('delete', $event));
    }

    public function test_life_groups_minister_can_manage_small_groups(): void
    {
        [$branch, $user] = $this->makeBranchWithMinister('life_groups');
        $this->actingAs($user);

        $group = SmallGroup::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->assertTrue($user->can('create', SmallGroup::class));
        $this->assertTrue($user->can('update', $group));
        $this->assertTrue($user->can('manageMembers', $group));
    }

    public function test_communications_minister_can_manage_settings(): void
    {
        [$branch, $user] = $this->makeBranchWithMinister('communications');
        $this->actingAs($user);

        $this->assertTrue($user->can('manageSettings', $branch));
    }
}
