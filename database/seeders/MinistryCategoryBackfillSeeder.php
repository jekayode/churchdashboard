<?php

namespace Database\Seeders;

use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistryCategoryBackfillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simple heuristic based on ministry name; safe and idempotent.
        Ministry::query()->whereNull('category')->chunkById(200, function ($chunk) {
            foreach ($chunk as $ministry) {
                $name = strtolower($ministry->name ?? '');
                $category = null;

                if (str_contains($name, 'life group') || str_contains($name, 'assimilation')) {
                    $category = 'life_groups';
                } elseif (str_contains($name, 'communication') || str_contains($name, 'media')) {
                    $category = 'communications';
                } elseif (str_contains($name, 'operation') || str_contains($name, 'service')) {
                    $category = 'operations';
                }

                if ($category) {
                    $ministry->update(['category' => $category]);
                }
            }
        });
    }
}
