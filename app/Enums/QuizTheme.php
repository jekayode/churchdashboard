<?php

declare(strict_types=1);

namespace App\Enums;

enum QuizTheme: string
{
    case Standard = 'standard';
    case Nigeria = 'nigeria';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Nigeria => 'Nigeria — green, white, green',
        };
    }
}
