<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DirectoryChangelogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DirectoryChangelogEntry>
 */
final class DirectoryChangelogEntryFactory extends Factory
{
    protected $model = DirectoryChangelogEntry::class;

    public function definition(): array
    {
        return [
            'version' => fake()->semver(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'published_at' => now(),
        ];
    }
}
