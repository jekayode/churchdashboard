<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessService>
 */
final class BusinessServiceFactory extends Factory
{
    protected $model = BusinessService::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'duration_text' => fake()->randomElement(['30 mins', '1 hour', '2 hours']),
            'price_text' => 'from ₦'.number_format(fake()->numberBetween(50000, 500000)),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
