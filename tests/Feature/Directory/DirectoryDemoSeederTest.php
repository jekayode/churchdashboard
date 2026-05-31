<?php

declare(strict_types=1);

namespace Tests\Feature\Directory;

use App\Enums\BusinessStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DirectoryDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_demo_seeder_creates_seed_data(): void
    {
        $this->seed(\Database\Seeders\DirectoryDemoSeeder::class);

        $this->assertDatabaseHas('directory_settings', [
            'announcement_active' => true,
        ]);

        $this->assertDatabaseHas('directory_categories', [
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('businesses', [
            'status' => BusinessStatus::Active->value,
        ]);
    }
}
