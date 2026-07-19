<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Quiz\QuizTimeline;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Quiz extends Model
{
    /** @use HasFactory<\Database\Factories\QuizFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Read from across a room and typed on a phone, so the alphabet leaves out
     * every pair that gets misread: O/0, I/1/L, S/5, B/8, G/6. Both sides of
     * each pair have to go — dropping only the letter still leaves the digit to
     * be mistyped as it.
     */
    private const CODE_ALPHABET = 'ACDEFHJKMNPQRTUVWXY23479';

    private const CODE_LENGTH = 5;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'code',
        'status',
        'seconds_per_question',
        'base_points',
        'reveal_seconds',
        'allow_guests',
        'started_at',
        'paused_at',
        'paused_ms',
        'finished_at',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'seconds_per_question' => 'integer',
        'base_points' => 'integer',
        'reveal_seconds' => 'integer',
        'paused_ms' => 'integer',
        'allow_guests' => 'boolean',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    /**
     * @return HasMany<QuizParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(QuizParticipant::class);
    }

    /**
     * @return HasManyThrough<QuizAnswer, QuizQuestion, $this>
     */
    public function answers(): HasManyThrough
    {
        return $this->hasManyThrough(QuizAnswer::class, QuizQuestion::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A code unused by any quiz that could still be joined. Finished quizzes
     * release their code back, so codes stay short instead of growing to stay
     * unique over years of monthly use.
     */
    public static function generateCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Participants ordered for the leaderboard: most points first, and equal
     * points split by who answered faster overall, so there is one winner.
     *
     * @return \Illuminate\Database\Eloquent\Builder<QuizParticipant>
     */
    public function leaderboardQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->participants()
            ->whereNull('removed_at')
            ->orderByDesc('score')
            ->orderBy('total_response_ms')
            ->orderBy('id')
            ->getQuery();
    }

    public function timeline(): QuizTimeline
    {
        return new QuizTimeline($this, $this->questions);
    }

    public function isJoinable(): bool
    {
        return in_array($this->status, ['lobby', 'running'], true);
    }

    public function scopeForBranch(\Illuminate\Database\Eloquent\Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }
}
