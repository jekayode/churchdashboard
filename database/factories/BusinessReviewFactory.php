<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\BusinessReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessReview>
 */
final class BusinessReviewFactory extends Factory
{
    protected $model = BusinessReview::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory()->active(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
            'status' => ReviewStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => ReviewStatus::Approved]);
    }
}
