<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum CompetitionStatus: string implements HasLabel, HasColor, HasIcon
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case CALCULATING = 'calculating';
    case ENDED = 'ended';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT => 'مسودة',
            self::ACTIVE => 'نشطة',
            self::CALCULATING => 'جاري الحساب',
            self::ENDED => 'منتهية',
            self::PUBLISHED => 'تم النشر',
            self::CANCELLED => 'ملغاة',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::CALCULATING => 'warning',
            self::ENDED => 'info',
            self::PUBLISHED => 'primary',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::ACTIVE => 'heroicon-o-play-circle',
            self::CALCULATING => 'heroicon-o-calculator',
            self::ENDED => 'heroicon-o-flag',
            self::PUBLISHED => 'heroicon-o-megaphone',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT]);
    }

    public function canActivate(): bool
    {
        return $this === self::DRAFT;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::ACTIVE]);
    }

    public function canPublish(): bool
    {
        return $this === self::ENDED;
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::ENDED, self::PUBLISHED, self::CANCELLED]);
    }
}
