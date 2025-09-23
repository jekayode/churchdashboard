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
        'whatsapp_provider',
        'whatsapp_config',
        'birthday_template_id',
        'anniversary_template_id',
        'auto_send_birthdays',
        'auto_send_anniversaries',
        'from_name',
        'from_email',
        'is_active',
    ];

    protected $casts = [
        'email_config' => 'array',
        'sms_config' => 'array',
        'whatsapp_config' => 'array',
        'auto_send_birthdays' => 'boolean',
        'auto_send_anniversaries' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function birthdayTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'birthday_template_id');
    }

    public function anniversaryTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'anniversary_template_id');
    }
}
