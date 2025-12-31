<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum WinnerType: string implements HasLabel, HasIcon
{
    case BRANCH = 'branch';
    case EMPLOYEE = 'employee';

    public function getLabel(): string
    {
        return match($this) {
            self::BRANCH => 'فرع',
            self::EMPLOYEE => 'موظف',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::BRANCH => 'heroicon-o-building-storefront',
            self::EMPLOYEE => 'heroicon-o-user',
        };
    }
}
