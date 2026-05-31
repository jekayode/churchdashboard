<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class BuilderSetting extends Model
{
    protected $fillable = [
        'whatsapp_group_link',
        'google_drive_link',
        'intro_text',
        'confirmation_body',
    ];

    public static function instance(): self
    {
        return self::query()->firstOrCreate([], [
            'intro_text' => 'Fill this short form and your free Business Starter Pack downloads immediately.',
            'confirmation_body' => 'Thank you for taking this step. Someone from the Lifepointe Business & Career Unit will reach out to you personally within 48 hours.',
        ]);
    }
}
