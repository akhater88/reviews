<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case STRIPE = 'stripe';
    case MOYASAR = 'moyasar';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::MOYASAR => 'Moyasar',
            self::MANUAL => 'دفع يدوي',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::MOYASAR => 'Moyasar',
            self::MANUAL => 'Manual Payment',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::STRIPE => 'heroicon-o-credit-card',
            self::MOYASAR => 'heroicon-o-banknotes',
            self::MANUAL => 'heroicon-o-document-text',
        };
    }

    public static function fromConfig(): self
    {
        $gateway = config('subscription.payment_gateway', 'manual');

        return self::tryFrom($gateway) ?? self::MANUAL;
    }

    public function isOnline(): bool
    {
        return in_array($this, [self::STRIPE, self::MOYASAR]);
    }
}
