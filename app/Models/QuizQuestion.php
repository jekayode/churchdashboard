<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuizQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\QuizQuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['quiz_id', 'position', 'text', 'time_limit_seconds', 'points'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'time_limit_seconds' => 'integer',
        'points' => 'integer',
    ];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return HasMany<QuizOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('position');
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function correctOption(): ?QuizOption
    {
        return $this->options->firstWhere('is_correct', true);
    }

    /** Falls back to the quiz setting when this question does not override it. */
    public function effectiveTimeLimit(): int
    {
        return $this->time_limit_seconds ?? $this->quiz->seconds_per_question;
    }

    public function effectivePoints(): int
    {
        return $this->points ?? $this->quiz->base_points;
    }
}
