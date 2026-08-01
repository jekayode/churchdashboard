<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\CoverageLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The dropdown was empty on the public guest form because the branch isn't
 * known until submit, and strict scoping returns nothing for a null/empty
 * branch. optionsForForm must never come back empty when any active location
 * exists.
 */
final class CoverageLocationOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_known_branch_gets_its_own_locations(): void
    {
        $branch = Branch::factory()->create();
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Eputu - Bogije']);

        $this->assertSame(['Eputu - Bogije'], CoverageLocation::optionsForForm($branch->id));
    }

    public function test_an_unknown_branch_falls_back_to_all_active_locations(): void
    {
        $branch = Branch::factory()->create();
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'UBA - Ogunfayo']);

        // This is exactly the public guest form's case: no branch chosen yet.
        $this->assertSame(['UBA - Ogunfayo'], CoverageLocation::optionsForForm(null));
    }

    public function test_a_branch_with_no_locations_falls_back_rather_than_empty(): void
    {
        $withAreas = Branch::factory()->create();
        CoverageLocation::factory()->create(['branch_id' => $withAreas->id, 'name' => 'Ajah Badore - General Paint']);
        $bare = Branch::factory()->create();

        $this->assertSame(['Ajah Badore - General Paint'], CoverageLocation::optionsForForm($bare->id));
    }

    public function test_inactive_locations_are_never_offered(): void
    {
        $branch = Branch::factory()->create();
        CoverageLocation::factory()->create(['branch_id' => $branch->id, 'name' => 'Retired Area', 'is_active' => false]);

        $this->assertSame([], CoverageLocation::optionsForForm($branch->id));
        $this->assertSame([], CoverageLocation::optionsForForm(null));
    }
}
