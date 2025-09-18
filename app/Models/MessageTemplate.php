<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'subject',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function campaignSteps(): HasMany
    {
        return $this->hasMany(EmailCampaignStep::class);
    }
}
