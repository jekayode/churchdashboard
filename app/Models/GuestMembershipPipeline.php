<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GuestMembershipPipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'interest_level',
        'pipeline_stage',
        'assigned_to',
        'contacted_at',
        'info_sent_at',
        'class_scheduled_at',
        'class_attended_at',
        'conversion_date',
        'notes',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'info_sent_at' => 'datetime',
        'class_scheduled_at' => 'datetime',
        'class_attended_at' => 'datetime',
        'conversion_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByStage($query, string $stage)
    {
        return $query->where('pipeline_stage', $stage);
    }

    public function scopeHighInterest($query)
    {
        return $query->where('interest_level', 'high');
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->whereHas('member', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByInterestLevel($query, string $level)
    {
        return $query->where('interest_level', $level);
    }

    public function scopeConverted($query)
    {
        return $query->where('pipeline_stage', 'converted');
    }

    public function scopeNotConverted($query)
    {
        return $query->where('pipeline_stage', '!=', 'converted');
    }

    // Helper methods
    public function moveToStage(string $stage): void
    {
        $this->update(['pipeline_stage' => $stage]);

        // Update timestamps based on stage
        switch ($stage) {
            case 'contacted':
                $this->update(['contacted_at' => now()]);
                break;
            case 'info_sent':
                $this->update(['info_sent_at' => now()]);
                break;
            case 'class_scheduled':
                $this->update(['class_scheduled_at' => now()]);
                break;
            case 'class_attended':
                $this->update(['class_attended_at' => now()]);
                break;
            case 'converted':
                $this->markConverted();
                break;
        }
    }

    public function markConverted(): void
    {
        $this->update([
            'pipeline_stage' => 'converted',
            'conversion_date' => now(),
        ]);

        // Update member status to member
        $this->member->update(['member_status' => 'member']);
    }

    public function markNotInterested(): void
    {
        $this->update(['pipeline_stage' => 'not_interested']);
    }

    public function isConverted(): bool
    {
        return $this->pipeline_stage === 'converted';
    }

    public function isHighInterest(): bool
    {
        return $this->interest_level === 'high';
    }
}
