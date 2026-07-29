<?php

declare(strict_types=1);

namespace Tests\Feature\Pastor;

use App\Models\Branch;
use App\Models\CoverageLocation;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CoverageLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    private function pastorOf(Branch $branch): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('branch_pastor');
        $branch->update(['pastor_id' => $user->id]);
        $user->assignRole('branch_pastor', $branch->id);

        return $user->fresh();
    }

    public function test_a_pastor_can_add_a_location(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $pastor = $this->pastorOf($branch);

        $this->actingAs($pastor)
            ->post(route('pastor.coverage-locations.store'), ['name' => 'Eputu - Bogije'])
            ->assertRedirect();

        $this->assertDatabaseHas('coverage_locations', [
            'branch_id' => $branch->id,
            'name' => 'Eputu - Bogije',
            'is_active' => true,
        ]);
    }

    public function test_the_same_location_cannot_be_added_twice_in_a_branch(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $pastor = $this->pastorOf($branch);
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Eputu - Bogije']);

        $this->actingAs($pastor)
            ->post(route('pastor.coverage-locations.store'), ['name' => 'Eputu - Bogije'])
            ->assertSessionHasErrors('name');
    }

    public function test_retiring_a_location_deactivates_rather_than_deletes_it(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $pastor = $this->pastorOf($branch);
        $location = CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Old Area']);

        $this->actingAs($pastor)->delete(route('pastor.coverage-locations.destroy', $location))->assertRedirect();

        // Kept, so a member who chose it does not lose their value.
        $this->assertDatabaseHas('coverage_locations', ['id' => $location->id, 'is_active' => false]);
    }

    public function test_a_pastor_cannot_touch_another_branchs_location(): void
    {
        $mine = Branch::factory()->create(['status' => 'active']);
        $pastor = $this->pastorOf($mine);
        $other = CoverageLocation::factory()->create(['branch_id' => Branch::factory()->create()->id]);

        $this->actingAs($pastor)
            ->put(route('pastor.coverage-locations.update', $other), ['name' => 'Hijacked', 'sort_order' => 1])
            ->assertNotFound();
    }

    public function test_only_active_locations_are_offered_and_in_order(): void
    {
        $branch = Branch::factory()->create();
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Second', 'sort_order' => 2]);
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'First', 'sort_order' => 1]);
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Retired', 'sort_order' => 3, 'is_active' => false]);

        $this->assertSame(['First', 'Second'], CoverageLocation::optionsForBranch($branch->id));
    }

    public function test_the_profile_dropdown_shows_the_managed_list(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Eputu - Bogije', 'sort_order' => 1]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('church_member', $branch->id);
        $member = Member::factory()->create(['user_id' => $user->id, 'branch_id' => $branch->id]);

        // The dropdown lives on the edit page, not the read-only details view.
        $this->actingAs($user->fresh())
            ->get(route('member.profile.edit'))
            ->assertOk()
            ->assertSee('Eputu - Bogije');
    }

    public function test_a_retired_value_a_member_still_holds_stays_selectable(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        // The list no longer offers "Old Area", but this member chose it before.
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Current Area', 'sort_order' => 1]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('church_member', $branch->id);
        $member = Member::factory()->create([
            'user_id' => $user->id, 'branch_id' => $branch->id, 'closest_location' => 'Old Area',
        ]);

        // Editing their profile must not silently blank a value that has since
        // been retired from the list.
        $this->actingAs($user->fresh())
            ->get(route('member.profile.edit'))
            ->assertOk()
            ->assertSee('Old Area');
    }

    public function test_the_management_page_renders(): void
    {
        $branch = Branch::factory()->create(['status' => 'active']);
        $pastor = $this->pastorOf($branch);
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Eputu - Bogije']);

        // A Blade mistake only shows when the page is rendered.
        $this->actingAs($pastor)
            ->get(route('pastor.coverage-locations'))
            ->assertOk()
            ->assertSee('Coverage Locations')
            ->assertSee('Eputu - Bogije');
    }

    public function test_an_ordinary_member_cannot_manage_locations(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('church_member');

        $response = $this->actingAs($user)->get(route('pastor.coverage-locations'));
        $this->assertNotSame(200, $response->status());
    }
}
