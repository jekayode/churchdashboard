<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MemberReadingProgress extends Model
{
    /** @use HasFactory<\Database\Factories\MemberReadingProgressFactory> */
    use HasFactory;

    protected $table = 'member_reading_progress';

    /**
     * @var list<string>
     */
    protected $fillable = ['member_id', 'reading_day_id', 'reading_plan_id', 'completed_on', 'completed_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'completed_on' => 'date',
        'completed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(ReadingDay::class, 'reading_day_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ReadingPlan::class, 'reading_plan_id');
    }
}
