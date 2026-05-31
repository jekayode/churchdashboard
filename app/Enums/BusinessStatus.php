<?php

declare(strict_types=1);

namespace App\Enums;

enum BusinessStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Inactive = 'inactive';
    case Rejected = 'rejected';
}
