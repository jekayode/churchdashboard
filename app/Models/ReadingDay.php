<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ReadingDay extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingDayFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reading_plan_id', 'day_number', 'month_day', 'label',
        'old_testament', 'new_testament', 'psalm', 'proverbs', 'passages',
        'title', 'focus_verse', 'body', 'reflection_prompt',
        'study_question_1', 'study_question_2', 'questions_updated_at', 'questions_updated_by', 'source_url',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'day_number' => 'integer',
        'passages' => 'array',
        'questions_updated_at' => 'datetime',
    ];

    /**
     * Whether the church has replaced the imported study questions.
     */
    public function hasOwnQuestions(): bool
    {
        return $this->questions_updated_at !== null;
    }

    /**
     * The pastor who last rewrote this day's questions.
     */
    public function questionsAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'questions_updated_by');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ReadingPlan::class, 'reading_plan_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(MemberReadingProgress::class);
    }

    /**
     * Every reference for the day, in reading order.
     *
     * @return list<array{group: string, reference: string}>
     */
    public function references(): array
    {
        $groups = [
            'Old Testament' => $this->old_testament,
            'New Testament' => $this->new_testament,
            'Psalm' => $this->psalm,
            'Proverbs' => $this->proverbs,
        ];

        $references = [];

        foreach ($groups as $group => $reference) {
            if (filled($reference)) {
                $references[] = ['group' => $group, 'reference' => $reference];
            }
        }

        // Plans that do not use the four-group split store a plain list.
        foreach ($this->passages ?? [] as $reference) {
            if (filled($reference)) {
                $references[] = ['group' => 'Reading', 'reference' => $reference];
            }
        }

        return $references;
    }

    /**
     * @return list<string>
     */
    public function studyQuestions(): array
    {
        return array_values(array_filter([
            $this->study_question_1,
            $this->study_question_2,
        ], fn (?string $q): bool => filled($q)));
    }
}
