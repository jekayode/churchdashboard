<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuizParticipant extends Model
{
    /** @use HasFactory<\Database\Factories\QuizParticipantFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'quiz_id',
        'member_id',
        'guest_token',
        'display_name',
        'score',
        'total_response_ms',
        'correct_count',
        'joined_at',
        'removed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
        'total_response_ms' => 'integer',
        'correct_count' => 'integer',
        'joined_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isGuest(): bool
    {
        return $this->member_id === null;
    }

    /** Recomputed from the answers rather than incremented, so a replayed or
     *  corrected answer can never drift the running total. */
    public function recalculateScore(): void
    {
        $totals = $this->answers()
            ->selectRaw('COALESCE(SUM(points_awarded), 0) as points')
            ->selectRaw('COALESCE(SUM(response_ms), 0) as response_ms')
            ->selectRaw('COALESCE(SUM(is_correct), 0) as correct')
            ->first();

        $this->forceFill([
            'score' => (int) $totals->points,
            'total_response_ms' => (int) $totals->response_ms,
            'correct_count' => (int) $totals->correct,
        ])->save();
    }
}
