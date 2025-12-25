<?php

namespace App\Enums;

enum BranchType: string
{
    case OWNED = 'owned';
    case COMPETITOR = 'competitor';

    public function label(): string
    {
        return match($this) {
            self::OWNED => 'فرعي',
            self::COMPETITOR => 'منافس',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::OWNED => 'heroicon-o-building-storefront',
            self::COMPETITOR => 'heroicon-o-eye',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OWNED => 'primary',
            self::COMPETITOR => 'gray',
        };
    }
}
