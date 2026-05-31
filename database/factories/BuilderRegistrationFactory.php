<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BuilderIndustry;
use App\Enums\BuilderRegistrationStatus;
use App\Enums\BusinessChallenge;
use App\Enums\BusinessStage;
use App\Enums\CacStatus;
use App\Models\BuilderRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuilderRegistration>
 */
final class BuilderRegistrationFactory extends Factory
{
    protected $model = BuilderRegistration::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'business_name' => fake()->company(),
            'business_description' => fake()->sentence(),
            'business_stage' => fake()->randomElement(BusinessStage::cases())->value,
            'industry' => BuilderIndustry::TechDigital->value,
            'industry_other' => null,
            'biggest_challenge' => fake()->randomElement(BusinessChallenge::cases())->value,
            'success_vision' => fake()->paragraph(),
            'cac_status' => fake()->randomElement(CacStatus::cases())->value,
            'status' => BuilderRegistrationStatus::New,
        ];
    }
}
