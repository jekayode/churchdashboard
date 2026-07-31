<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\CoverageLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoverageLocation>
 */
final class CoverageLocationFactory extends Factory
{
    protected $model = CoverageLocation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => $this->faker->unique()->streetName(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
