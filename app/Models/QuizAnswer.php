<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QuizAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\QuizAnswerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'quiz_question_id',
        'quiz_participant_id',
        'quiz_option_id',
        'response_ms',
        'is_correct',
        'points_awarded',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'response_ms' => 'integer',
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
    ];

    /**
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    /**
     * @return BelongsTo<QuizParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(QuizParticipant::class, 'quiz_participant_id');
    }

    /**
     * @return BelongsTo<QuizOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
}
