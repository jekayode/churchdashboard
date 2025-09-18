<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CommunicationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'email_provider',
        'email_config',
        'sms_provider',
        'sms_config',
        'from_name',
        'from_email',
        'is_active',
    ];

    protected $casts = [
        'email_config' => 'array',
        'sms_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
