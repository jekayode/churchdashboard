<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\DirectoryCategory;
use App\Models\DirectorySetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BusinessDirectoryFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_settings_singleton_is_created(): void
    {
        $settings = DirectorySetting::instance();

        $this->assertDatabaseHas('directory_settings', ['id' => $settings->id]);
        $this->assertSame('#F1592A', $settings->primary_color);
    }

    public function test_business_belongs_to_owner_and_categories(): void
    {
        $owner = User::factory()->create();
        $category = DirectoryCategory::factory()->create();
        $business = Business::factory()->for($owner, 'owner')->create([
            'status' => BusinessStatus::Active,
        ]);
        $business->categories()->attach($category);

        $this->assertTrue($owner->ownsBusinesses());
        $this->assertCount(1, $business->categories);
        $this->assertSame($owner->id, $business->owner->id);
    }

    public function test_reserved_slugs_are_detected(): void
    {
        $this->assertTrue(Business::slugTaken('search'));
        $this->assertFalse(Business::slugTaken('my-cool-shop'));
    }

    public function test_directory_admin_role_exists_after_seeder(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'directory_admin']);
    }

    public function test_branch_pastor_is_directory_admin(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'branch_pastor')->first()
            ?? Role::factory()->create(['name' => 'branch_pastor', 'display_name' => 'Branch Pastor']);
        $user->assignRole('branch_pastor');

        $this->assertTrue($user->isDirectoryAdmin());
    }
}
