<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'trigger_event',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(EmailCampaignStep::class, 'campaign_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(EmailCampaignEnrollment::class, 'campaign_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByTrigger($query, string $triggerEvent)
    {
        return $query->where('trigger_event', $triggerEvent);
    }

    public function getTotalEnrollmentsAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getActiveEnrollmentsAttribute(): int
    {
        return $this->enrollments()->whereNull('completed_at')->count();
    }

    public function getCompletedEnrollmentsAttribute(): int
    {
        return $this->enrollments()->whereNotNull('completed_at')->count();
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->whereNull('completed_at');
    }

    public function completedEnrollments(): HasMany
    {
        return $this->enrollments()->whereNotNull('completed_at');
    }
}
