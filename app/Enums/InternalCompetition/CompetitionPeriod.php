<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;

enum CompetitionPeriod: string implements HasLabel
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';

    public function getLabel(): string
    {
        return match($this) {
            self::MONTHLY => 'شهري',
            self::QUARTERLY => 'ربع سنوي',
        };
    }

    public function getDurationInDays(): int
    {
        return match($this) {
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::MONTHLY => 'heroicon-o-calendar',
            self::QUARTERLY => 'heroicon-o-calendar-days',
        };
    }
}
