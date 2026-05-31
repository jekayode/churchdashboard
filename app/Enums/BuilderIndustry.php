<?php

declare(strict_types=1);

namespace App\Enums;

enum BuilderIndustry: string
{
    case FashionBeauty = 'fashion_beauty';
    case FoodBeverages = 'food_beverages';
    case TechDigital = 'tech_digital';
    case CreativeArts = 'creative_arts';
    case HealthWellness = 'health_wellness';
    case EducationTraining = 'education_training';
    case RetailEcommerce = 'retail_ecommerce';
    case ProfessionalServices = 'professional_services';
    case EventsHospitality = 'events_hospitality';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FashionBeauty => 'Fashion and beauty',
            self::FoodBeverages => 'Food and beverages',
            self::TechDigital => 'Tech and digital services',
            self::CreativeArts => 'Creative arts and media',
            self::HealthWellness => 'Health and wellness',
            self::EducationTraining => 'Education and training',
            self::RetailEcommerce => 'Retail and e-commerce',
            self::ProfessionalServices => 'Professional services',
            self::EventsHospitality => 'Events and hospitality',
            self::Other => 'Other (please specify)',
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
