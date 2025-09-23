<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EmailCampaignEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'current_step',
        'next_send_at',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'next_send_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeDueForSending($query)
    {
        return $query->whereNotNull('next_send_at')
            ->where('next_send_at', '<=', now())
            ->whereNull('completed_at');
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function getCurrentStepModel(): ?EmailCampaignStep
    {
        return EmailCampaignStep::where('campaign_id', $this->campaign_id)
            ->where('step_order', $this->current_step)
            ->first();
    }

    public function getNextStepModel(): ?EmailCampaignStep
    {
        return EmailCampaignStep::where('campaign_id', $this->campaign_id)
            ->where('step_order', $this->current_step + 1)
            ->first();
    }

    public function advanceToNextStep(): bool
    {
        $nextStep = $this->getNextStepModel();

        if (! $nextStep) {
            // No more steps, mark as completed
            $this->update([
                'completed_at' => now(),
                'next_send_at' => null,
            ]);

            return false;
        }

        // Advance to next step
        $this->update([
            'current_step' => $nextStep->step_order,
            'next_send_at' => now()->addDays($nextStep->delay_days),
        ]);

        return true;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'next_send_at' => null,
        ]);
    }

    public function getProgressPercentageAttribute(): float
    {
        $totalSteps = $this->campaign->steps()->count();
        if ($totalSteps === 0) {
            return 0;
        }

        if ($this->completed_at) {
            return 100;
        }

        return ($this->current_step / $totalSteps) * 100;
    }

    public function getIsCompletedAttribute(): bool
    {
        return ! is_null($this->completed_at);
    }

    public function getIsActiveAttribute(): bool
    {
        return is_null($this->completed_at);
    }
}
