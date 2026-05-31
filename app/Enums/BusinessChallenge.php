<?php

declare(strict_types=1);

namespace App\Enums;

enum BusinessChallenge: string
{
    case NoClearPlan = 'no_clear_plan';
    case FindingCustomers = 'finding_customers';
    case Marketing = 'marketing';
    case InconsistentRevenue = 'inconsistent_revenue';
    case NotStarted = 'not_started';

    public function label(): string
    {
        return match ($this) {
            self::NoClearPlan => 'I do not have a clear plan for my business',
            self::FindingCustomers => 'I cannot find and keep customers consistently',
            self::Marketing => 'I do not know how to market my business effectively',
            self::InconsistentRevenue => 'My revenue is inconsistent, and I do not know why',
            self::NotStarted => 'I have not started yet and do not know where to begin',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
