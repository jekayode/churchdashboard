<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReadingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingPlan>
 */
final class ReadingPlanFactory extends Factory
{
    protected $model = ReadingPlan::class;

    public function definition(): array
    {
        return [
            'branch_id' => null,
            'name' => ucwords($this->faker->unique()->words(2, true)),
            'description' => $this->faker->sentence(),
            'type' => ReadingPlan::TYPE_PASSAGES,
            'is_annual' => false,
            'length_days' => 0,
            'tone' => 'orange',
            'is_published' => true,
            'is_default' => false,
        ];
    }

    public function annual(): self
    {
        return $this->state(fn (): array => ['is_annual' => true]);
    }

    public function devotional(): self
    {
        return $this->state(fn (): array => ['type' => ReadingPlan::TYPE_DEVOTIONAL]);
    }

    public function unpublished(): self
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
