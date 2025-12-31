<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TenantEnrollmentMode: string implements HasLabel, HasColor
{
    case MANUAL = 'manual';
    case AUTO_ALL = 'auto_all';
    case AUTO_NEW = 'auto_new';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MANUAL => 'اختيار يدوي',
            self::AUTO_ALL => 'جميع المستأجرين تلقائياً',
            self::AUTO_NEW => 'تلقائي + المستأجرين الجدد',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MANUAL => 'info',
            self::AUTO_ALL => 'success',
            self::AUTO_NEW => 'warning',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::MANUAL => 'اختر المستأجرين يدوياً من القائمة',
            self::AUTO_ALL => 'تسجيل جميع المستأجرين الحاليين تلقائياً',
            self::AUTO_NEW => 'تسجيل الجميع + أي مستأجر جديد ينضم لاحقاً',
        };
    }
}
