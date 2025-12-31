<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;

enum BenchmarkPeriodType: string implements HasLabel
{
    case DURING_COMPETITION = 'during_competition';
    case BEFORE_COMPETITION = 'before_competition';

    public function getLabel(): string
    {
        return match($this) {
            self::DURING_COMPETITION => 'أثناء المسابقة',
            self::BEFORE_COMPETITION => 'قبل المسابقة',
        };
    }
}
