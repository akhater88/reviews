<?php

namespace App\Enums;

enum BranchSource: string
{
    case GOOGLE_BUSINESS = 'google_business';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match($this) {
            self::GOOGLE_BUSINESS => 'Google Business',
            self::MANUAL => 'إضافة يدوية',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::GOOGLE_BUSINESS => 'heroicon-o-check-badge',
            self::MANUAL => 'heroicon-o-pencil-square',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GOOGLE_BUSINESS => 'success',
            self::MANUAL => 'warning',
        };
    }

    public function canReply(): bool
    {
        return $this === self::GOOGLE_BUSINESS;
    }
}
