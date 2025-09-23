<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EmailCampaignStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'step_order',
        'delay_days',
        'template_id',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'delay_days' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('step_order');
    }

    public function getDelayInHoursAttribute(): int
    {
        return $this->delay_days * 24;
    }

    public function getIsFirstStepAttribute(): bool
    {
        return $this->step_order === 1;
    }

    public function getNextStep(): ?self
    {
        return self::where('campaign_id', $this->campaign_id)
            ->where('step_order', $this->step_order + 1)
            ->first();
    }

    public function getPreviousStep(): ?self
    {
        return self::where('campaign_id', $this->campaign_id)
            ->where('step_order', $this->step_order - 1)
            ->first();
    }
}
