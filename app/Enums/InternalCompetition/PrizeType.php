<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum PrizeType: string implements HasLabel, HasIcon
{
    case DISPLAY = 'display';
    case PHYSICAL = 'physical';

    public function getLabel(): string
    {
        return match($this) {
            self::DISPLAY => 'عرض فقط',
            self::PHYSICAL => 'جائزة مادية',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::DISPLAY => 'heroicon-o-trophy',
            self::PHYSICAL => 'heroicon-o-gift',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::DISPLAY => 'شهادة أو اعتراف رمزي',
            self::PHYSICAL => 'جائزة مادية يتم تتبع تسليمها',
        };
    }

    public function requiresTracking(): bool
    {
        return $this === self::PHYSICAL;
    }
}
