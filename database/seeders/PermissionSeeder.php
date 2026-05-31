<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

final class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $item) {
            Permission::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'group' => $item['group'],
                    'label' => $item['label'],
                    'description' => $item['description'],
                    'is_dangerous' => $item['is_dangerous'],
                ]
            );
        }

        $this->command?->info('Permissions catalog seeded.');
    }
}
