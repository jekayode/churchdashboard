<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DirectorySetting;
use Illuminate\Database\Seeder;

final class DirectorySettingSeeder extends Seeder
{
    public function run(): void
    {
        DirectorySetting::instance();

        $this->command?->info('Directory settings initialized.');
    }
}
