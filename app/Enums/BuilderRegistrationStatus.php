<?php

declare(strict_types=1);

namespace App\Enums;

enum BuilderRegistrationStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
}
