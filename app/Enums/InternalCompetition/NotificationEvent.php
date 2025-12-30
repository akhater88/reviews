<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum NotificationEvent: string implements HasLabel, HasIcon
{
    case START = 'start';
    case REMINDER = 'reminder';
    case PROGRESS = 'progress';
    case ENDING_SOON = 'ending_soon';
    case ENDED = 'ended';
    case WINNER = 'winner';

    public function getLabel(): string
    {
        return match($this) {
            self::START => 'بداية المسابقة',
            self::REMINDER => 'تذكير',
            self::PROGRESS => 'تحديث التقدم',
            self::ENDING_SOON => 'قرب الانتهاء',
            self::ENDED => 'انتهاء المسابقة',
            self::WINNER => 'إعلان الفائز',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::START => 'heroicon-o-play',
            self::REMINDER => 'heroicon-o-bell',
            self::PROGRESS => 'heroicon-o-chart-bar',
            self::ENDING_SOON => 'heroicon-o-exclamation-triangle',
            self::ENDED => 'heroicon-o-flag',
            self::WINNER => 'heroicon-o-trophy',
        };
    }

    public function defaultTemplate(): string
    {
        return match($this) {
            self::START => 'internal_competition_start',
            self::REMINDER => 'internal_competition_reminder',
            self::PROGRESS => 'internal_competition_progress',
            self::ENDING_SOON => 'internal_competition_ending_soon',
            self::ENDED => 'internal_competition_ended',
            self::WINNER => 'internal_competition_winner',
        };
    }
}
