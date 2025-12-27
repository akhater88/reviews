<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case TRIAL = 'trial';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case GRACE_PERIOD = 'grace_period';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::TRIAL => 'فترة تجريبية',
            self::ACTIVE => 'نشط',
            self::PAST_DUE => 'متأخر السداد',
            self::GRACE_PERIOD => 'فترة سماح',
            self::CANCELLED => 'ملغي',
            self::EXPIRED => 'منتهي',
            self::SUSPENDED => 'موقوف',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::TRIAL => 'Trial',
            self::ACTIVE => 'Active',
            self::PAST_DUE => 'Past Due',
            self::GRACE_PERIOD => 'Grace Period',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TRIAL => 'info',
            self::ACTIVE => 'success',
            self::PAST_DUE => 'warning',
            self::GRACE_PERIOD => 'warning',
            self::CANCELLED => 'danger',
            self::EXPIRED => 'danger',
            self::SUSPENDED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TRIAL => 'heroicon-o-clock',
            self::ACTIVE => 'heroicon-o-check-circle',
            self::PAST_DUE => 'heroicon-o-exclamation-triangle',
            self::GRACE_PERIOD => 'heroicon-o-exclamation-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::EXPIRED => 'heroicon-o-x-circle',
            self::SUSPENDED => 'heroicon-o-pause-circle',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::TRIAL, self::ACTIVE, self::GRACE_PERIOD]);
    }

    public function canAccessFeatures(): bool
    {
        return in_array($this, [self::TRIAL, self::ACTIVE, self::PAST_DUE, self::GRACE_PERIOD]);
    }
}
