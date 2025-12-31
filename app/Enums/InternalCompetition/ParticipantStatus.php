<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum ParticipantStatus: string implements HasLabel, HasColor
{
    case ACTIVE = 'active';
    case WITHDRAWN = 'withdrawn';

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'نشط',
            self::WITHDRAWN => 'منسحب',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::WITHDRAWN => 'danger',
        };
    }
}
