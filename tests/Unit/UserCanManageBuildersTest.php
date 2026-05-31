<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserCanManageBuildersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function business_care_leader_can_manage_builders(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->where('name', 'business_care_leader')->first();
        $user->roles()->attach($role->id, ['branch_id' => null]);

        $this->assertTrue($user->canManageBuilders());
    }

    #[Test]
    public function care_division_ministry_leader_can_manage_builders(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id, 'branch_id' => $branch->id]);

        Ministry::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Care Division',
            'leader_id' => $member->id,
            'status' => 'active',
        ]);

        $this->assertTrue($user->canManageBuilders());
    }

    #[Test]
    public function regular_church_member_cannot_manage_builders(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->where('name', 'church_member')->first();
        $user->roles()->attach($role->id, ['branch_id' => null]);

        $this->assertFalse($user->canManageBuilders());
    }
}
