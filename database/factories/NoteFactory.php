<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
final class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'notable_type' => null,
            'notable_id' => null,
            'title' => null,
            'body' => $this->faker->paragraph(),
        ];
    }
}
