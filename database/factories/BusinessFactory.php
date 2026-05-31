<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
final class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'owner_user_id' => User::factory(),
            'name' => $name,
            'slug' => Business::generateSlug($name),
            'tagline' => fake()->catchPhrase(),
            'description' => fake()->paragraphs(2, true),
            'phone' => fake()->phoneNumber(),
            'whatsapp_number' => fake()->numerify('234##########'),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'Nigeria',
            'status' => BusinessStatus::Draft,
            'is_featured' => false,
            'views_count' => 0,
            'likes_count' => 0,
            'reviews_count' => 0,
            'average_rating' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => BusinessStatus::Active,
            'approved_at' => now(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'featured_until' => now()->addMonth(),
        ]);
    }
}
