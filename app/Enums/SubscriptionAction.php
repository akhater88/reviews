<?php

namespace App\Enums;

enum SubscriptionAction: string
{
    case CREATED = 'created';
    case UPGRADED = 'upgraded';
    case DOWNGRADED = 'downgraded';
    case RENEWED = 'renewed';
    case EXTENDED = 'extended';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case REACTIVATED = 'reactivated';
    case TRIAL_STARTED = 'trial_started';
    case TRIAL_ENDED = 'trial_ended';
    case TRIAL_CONVERTED = 'trial_converted';
    case GRACE_STARTED = 'grace_started';
    case GRACE_PERIOD_STARTED = 'grace_period_started';
    case PLAN_CHANGE_SCHEDULED = 'plan_change_scheduled';
    case SUSPENDED = 'suspended';
    case PAYMENT_FAILED = 'payment_failed';
    case PAYMENT_SUCCEEDED = 'payment_succeeded';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'تم الإنشاء',
            self::UPGRADED => 'تمت الترقية',
            self::DOWNGRADED => 'تم التخفيض',
            self::RENEWED => 'تم التجديد',
            self::EXTENDED => 'تم التمديد',
            self::CANCELLED => 'تم الإلغاء',
            self::EXPIRED => 'انتهى',
            self::REACTIVATED => 'تم إعادة التفعيل',
            self::TRIAL_STARTED => 'بدأت الفترة التجريبية',
            self::TRIAL_ENDED => 'انتهت الفترة التجريبية',
            self::TRIAL_CONVERTED => 'تحويل من تجريبي',
            self::GRACE_STARTED => 'بدأت فترة السماح',
            self::GRACE_PERIOD_STARTED => 'بدأت فترة السماح',
            self::PLAN_CHANGE_SCHEDULED => 'تغيير مجدول',
            self::SUSPENDED => 'تم الإيقاف',
            self::PAYMENT_FAILED => 'فشل الدفع',
            self::PAYMENT_SUCCEEDED => 'نجح الدفع',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::UPGRADED => 'Upgraded',
            self::DOWNGRADED => 'Downgraded',
            self::RENEWED => 'Renewed',
            self::EXTENDED => 'Extended',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::REACTIVATED => 'Reactivated',
            self::TRIAL_STARTED => 'Trial Started',
            self::TRIAL_ENDED => 'Trial Ended',
            self::TRIAL_CONVERTED => 'Trial Converted',
            self::GRACE_STARTED => 'Grace Period Started',
            self::GRACE_PERIOD_STARTED => 'Grace Period Started',
            self::PLAN_CHANGE_SCHEDULED => 'Plan Change Scheduled',
            self::SUSPENDED => 'Suspended',
            self::PAYMENT_FAILED => 'Payment Failed',
            self::PAYMENT_SUCCEEDED => 'Payment Succeeded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATED, self::UPGRADED, self::RENEWED, self::EXTENDED, self::REACTIVATED, self::TRIAL_CONVERTED, self::PAYMENT_SUCCEEDED => 'success',
            self::DOWNGRADED, self::TRIAL_STARTED, self::TRIAL_ENDED, self::GRACE_STARTED, self::GRACE_PERIOD_STARTED, self::PLAN_CHANGE_SCHEDULED => 'warning',
            self::CANCELLED, self::EXPIRED, self::SUSPENDED, self::PAYMENT_FAILED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CREATED => 'heroicon-o-plus-circle',
            self::UPGRADED => 'heroicon-o-arrow-trending-up',
            self::DOWNGRADED => 'heroicon-o-arrow-trending-down',
            self::RENEWED => 'heroicon-o-arrow-path',
            self::EXTENDED => 'heroicon-o-calendar-days',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::EXPIRED => 'heroicon-o-clock',
            self::REACTIVATED => 'heroicon-o-arrow-uturn-up',
            self::TRIAL_STARTED => 'heroicon-o-play',
            self::TRIAL_ENDED => 'heroicon-o-stop',
            self::TRIAL_CONVERTED => 'heroicon-o-check-badge',
            self::GRACE_STARTED => 'heroicon-o-exclamation-triangle',
            self::GRACE_PERIOD_STARTED => 'heroicon-o-exclamation-triangle',
            self::PLAN_CHANGE_SCHEDULED => 'heroicon-o-calendar',
            self::SUSPENDED => 'heroicon-o-pause-circle',
            self::PAYMENT_FAILED => 'heroicon-o-credit-card',
            self::PAYMENT_SUCCEEDED => 'heroicon-o-banknotes',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [
            self::CREATED,
            self::UPGRADED,
            self::RENEWED,
            self::EXTENDED,
            self::REACTIVATED,
            self::TRIAL_CONVERTED,
            self::PAYMENT_SUCCEEDED,
        ]);
    }

    public function isNegative(): bool
    {
        return in_array($this, [
            self::CANCELLED,
            self::EXPIRED,
            self::SUSPENDED,
            self::PAYMENT_FAILED,
        ]);
    }
}
