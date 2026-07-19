<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    private function memberUser(?Branch $branch = null): array
    {
        $branch ??= Branch::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('church_member', $branch->id);

        $member = Member::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);

        return [$user, $member, $branch];
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_member_can_view_own_profile(): void
    {
        [$user, $member, $branch] = $this->memberUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.email', $member->email)
            ->assertJsonPath('data.branch.id', $branch->id)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'first_name', 'surname', 'email', 'phone', 'member_status', 'branch'],
            ]);
    }

    public function test_user_without_member_profile_gets_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/me')->assertNotFound();
    }

    public function test_member_can_update_own_profile(): void
    {
        [$user, $member] = $this->memberUser();
        Sanctum::actingAs($user);

        $this->putJson('/api/me/profile', [
            'first_name' => 'Updated',
            'surname' => 'Person',
            'occupation' => 'Teacher',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.occupation', 'Teacher');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'first_name' => 'Updated',
            'occupation' => 'Teacher',
        ]);
    }

    public function test_profile_update_validates_input(): void
    {
        [$user] = $this->memberUser();
        Sanctum::actingAs($user);

        $this->putJson('/api/me/profile', [
            'email' => 'not-an-email',
            'gender' => 'invalid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'gender']);
    }

    public function test_member_cannot_change_leadership_managed_fields(): void
    {
        [$user, $member, $branch] = $this->memberUser();
        $otherBranch = Branch::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/me/profile', [
            'first_name' => 'Legit',
            'branch_id' => $otherBranch->id,
            'member_status' => 'leader',
            'growth_level' => 'leader',
        ])->assertOk();

        // Those fields are not in the allow-list, so they must be untouched.
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'branch_id' => $branch->id,
            'member_status' => $member->member_status,
            'growth_level' => $member->growth_level,
        ]);
    }
}
