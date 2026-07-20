<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QuizOption extends Model
{
    /** @use HasFactory<\Database\Factories\QuizOptionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['quiz_question_id', 'position', 'text', 'is_correct'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'is_correct' => 'boolean',
    ];

    /**
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
