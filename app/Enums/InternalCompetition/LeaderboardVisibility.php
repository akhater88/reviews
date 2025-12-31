<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum LeaderboardVisibility: string implements HasLabel, HasIcon
{
    case ALWAYS = 'always';
    case AFTER_END = 'after_end';
    case HIDDEN = 'hidden';

    public function getLabel(): string
    {
        return match($this) {
            self::ALWAYS => 'مرئي دائماً',
            self::AFTER_END => 'مرئي بعد الانتهاء',
            self::HIDDEN => 'مخفي (للإدارة فقط)',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::ALWAYS => 'heroicon-o-eye',
            self::AFTER_END => 'heroicon-o-clock',
            self::HIDDEN => 'heroicon-o-eye-slash',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ALWAYS => 'يمكن للمشاركين رؤية ترتيبهم في أي وقت',
            self::AFTER_END => 'الترتيب مخفي أثناء المسابقة ويظهر بعد انتهائها',
            self::HIDDEN => 'الترتيب مخفي دائماً ويراه المدير فقط',
        };
    }
}
