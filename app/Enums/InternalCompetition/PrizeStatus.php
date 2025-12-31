<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum PrizeStatus: string implements HasLabel, HasColor, HasIcon
{
    case ANNOUNCED = 'announced';
    case CLAIMED = 'claimed';
    case PROCESSING = 'processing';
    case DELIVERED = 'delivered';

    public function getLabel(): string
    {
        return match($this) {
            self::ANNOUNCED => 'تم الإعلان',
            self::CLAIMED => 'تم المطالبة',
            self::PROCESSING => 'قيد التجهيز',
            self::DELIVERED => 'تم التسليم',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::ANNOUNCED => 'info',
            self::CLAIMED => 'warning',
            self::PROCESSING => 'primary',
            self::DELIVERED => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::ANNOUNCED => 'heroicon-o-megaphone',
            self::CLAIMED => 'heroicon-o-hand-raised',
            self::PROCESSING => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-circle',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::ANNOUNCED => in_array($newStatus, [self::CLAIMED, self::PROCESSING]),
            self::CLAIMED => in_array($newStatus, [self::PROCESSING]),
            self::PROCESSING => in_array($newStatus, [self::DELIVERED]),
            self::DELIVERED => false,
        };
    }
}
