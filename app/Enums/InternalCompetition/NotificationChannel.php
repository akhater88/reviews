<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum NotificationChannel: string implements HasLabel, HasIcon
{
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';

    public function getLabel(): string
    {
        return match($this) {
            self::WHATSAPP => 'واتساب',
            self::EMAIL => 'بريد إلكتروني',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::WHATSAPP => 'heroicon-o-chat-bubble-left-ellipsis',
            self::EMAIL => 'heroicon-o-envelope',
        };
    }
}
