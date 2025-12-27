<?php

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case LIFETIME = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'شهري',
            self::YEARLY => 'سنوي',
            self::LIFETIME => 'مدى الحياة',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
            self::LIFETIME => 'Lifetime',
        };
    }

    public function months(): int
    {
        return match ($this) {
            self::MONTHLY => 1,
            self::YEARLY => 12,
            self::LIFETIME => 1200, // ~100 years
        };
    }

    public function discountLabel(): ?string
    {
        return match ($this) {
            self::YEARLY => 'وفر 17%',
            default => null,
        };
    }

    public function discountLabelEn(): ?string
    {
        return match ($this) {
            self::YEARLY => 'Save 17%',
            default => null,
        };
    }
}
