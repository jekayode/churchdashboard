<?php

declare(strict_types=1);

namespace App\Enums;

enum CacStatus: string
{
    case FullyRegistered = 'fully_registered';
    case BusinessNameRegistered = 'business_name_registered';
    case PlanningToRegister = 'planning_to_register';
    case NeedHelp = 'need_help';
    case DidNotKnow = 'did_not_know';

    public function label(): string
    {
        return match ($this) {
            self::FullyRegistered => 'Yes, fully registered (Company LTD)',
            self::BusinessNameRegistered => 'Yes, registered (Business Name)',
            self::PlanningToRegister => 'No, but I am planning to register soon',
            self::NeedHelp => 'No, and I would like help with registration',
            self::DidNotKnow => 'I did not know I needed to register',
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
