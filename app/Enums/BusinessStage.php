<?php

declare(strict_types=1);

namespace App\Enums;

enum BusinessStage: string
{
    case IdeaNotStarted = 'idea_not_started';
    case StartedNoCustomers = 'started_no_customers';
    case CustomersNotConsistent = 'customers_not_consistent';
    case RevenueNotGrowing = 'revenue_not_growing';

    public function label(): string
    {
        return match ($this) {
            self::IdeaNotStarted => 'I have an idea, but I have not started yet',
            self::StartedNoCustomers => 'I have started, but I have no paying customers yet',
            self::CustomersNotConsistent => 'I have paying customers, but the business is not consistent',
            self::RevenueNotGrowing => 'I am making consistent revenue, but not growing',
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
