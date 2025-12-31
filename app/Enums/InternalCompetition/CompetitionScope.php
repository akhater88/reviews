<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum CompetitionScope: string implements HasLabel, HasColor, HasIcon
{
    case SINGLE_TENANT = 'single_tenant';
    case MULTI_TENANT = 'multi_tenant';

    public function getLabel(): string
    {
        return match($this) {
            self::SINGLE_TENANT => 'مسابقة داخلية',
            self::MULTI_TENANT => 'مسابقة متعددة المستأجرين',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::SINGLE_TENANT => 'info',
            self::MULTI_TENANT => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::SINGLE_TENANT => 'heroicon-o-building-storefront',
            self::MULTI_TENANT => 'heroicon-o-building-office-2',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SINGLE_TENANT => 'مسابقة بين فروع مستأجر واحد',
            self::MULTI_TENANT => 'مسابقة بين فروع عدة مستأجرين',
        };
    }
}
