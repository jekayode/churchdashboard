<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProduct>
 */
final class BusinessProductFactory extends Factory
{
    protected $model = BusinessProduct::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price_text' => '$'.fake()->numberBetween(10, 200),
            'sort_order' => 0,
            'is_active' => true,
            'likes_count' => 0,
        ];
    }
}
