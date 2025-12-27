<?php

namespace App\Enums;

enum FeatureCategory: string
{
    case REVIEWS = 'reviews';
    case ANALYTICS = 'analytics';
    case AI = 'ai';
    case EXPORT = 'export';
    case INTEGRATION = 'integration';
    case SUPPORT = 'support';

    public function label(): string
    {
        return match ($this) {
            self::REVIEWS => 'المراجعات',
            self::ANALYTICS => 'التحليلات',
            self::AI => 'الذكاء الاصطناعي',
            self::EXPORT => 'التصدير',
            self::INTEGRATION => 'التكامل',
            self::SUPPORT => 'الدعم',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::REVIEWS => 'Reviews',
            self::ANALYTICS => 'Analytics',
            self::AI => 'AI Features',
            self::EXPORT => 'Export',
            self::INTEGRATION => 'Integration',
            self::SUPPORT => 'Support',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::REVIEWS => 'heroicon-o-chat-bubble-left-right',
            self::ANALYTICS => 'heroicon-o-chart-bar',
            self::AI => 'heroicon-o-sparkles',
            self::EXPORT => 'heroicon-o-arrow-down-tray',
            self::INTEGRATION => 'heroicon-o-puzzle-piece',
            self::SUPPORT => 'heroicon-o-lifebuoy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REVIEWS => 'primary',
            self::ANALYTICS => 'info',
            self::AI => 'warning',
            self::EXPORT => 'success',
            self::INTEGRATION => 'gray',
            self::SUPPORT => 'danger',
        };
    }
}
