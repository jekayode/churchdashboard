<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DirectoryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DirectoryCategory>
 */
final class DirectoryCategoryFactory extends Factory
{
    protected $model = DirectoryCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => 'building-storefront',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
