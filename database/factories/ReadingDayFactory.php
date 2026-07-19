<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReadingDay;
use App\Models\ReadingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingDay>
 */
final class ReadingDayFactory extends Factory
{
    protected $model = ReadingDay::class;

    public function definition(): array
    {
        return [
            'reading_plan_id' => ReadingPlan::factory(),
            'day_number' => 1,
            'month_day' => null,
            'label' => 'Day 1',
            'old_testament' => 'GENESIS 1:1-2:25',
            'new_testament' => 'MATTHEW 1:1-2:12',
            'psalm' => 'PSALM 1:1-1:6',
            'proverbs' => 'PROVERBS 1:1-1:6',
            'study_question_1' => 'What stood out to you today?',
            'study_question_2' => null,
        ];
    }
}
