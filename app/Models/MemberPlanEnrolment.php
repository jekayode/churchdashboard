<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MemberPlanEnrolment extends Model
{
    /** @use HasFactory<\Database\Factories\MemberPlanEnrolmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['member_id', 'reading_plan_id', 'started_on', 'is_active', 'completed_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'started_on' => 'date',
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ReadingPlan::class, 'reading_plan_id');
    }
}
