<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum NotificationStatus: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'قيد الانتظار',
            self::SENT => 'تم الإرسال',
            self::FAILED => 'فشل',
            self::SKIPPED => 'تم التخطي',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::PENDING => 'warning',
            self::SENT => 'success',
            self::FAILED => 'danger',
            self::SKIPPED => 'gray',
        };
    }
}
