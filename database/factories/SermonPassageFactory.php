<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sermon;
use App\Models\SermonPassage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SermonPassage>
 */
final class SermonPassageFactory extends Factory
{
    protected $model = SermonPassage::class;

    public function definition(): array
    {
        $book = $this->faker->randomElement(['Psalm', 'Romans', 'John', 'Proverbs']);
        $chapter = $this->faker->numberBetween(1, 40);

        return [
            'sermon_id' => Sermon::factory(),
            'reference' => $book.' '.$chapter.':1-10',
            'book' => $book,
            'chapter' => $chapter,
            'verses' => '1-10',
            'position' => 0,
        ];
    }
}
