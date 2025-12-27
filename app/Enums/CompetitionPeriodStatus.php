<?php

namespace App\Enums;

enum CompetitionPeriodStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ANALYZING = 'analyzing';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::ACTIVE => 'نشط',
            self::ANALYZING => 'جاري التحليل',
            self::COMPLETED => 'مكتمل',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::ANALYZING => 'warning',
            self::COMPLETED => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->toArray();
    }
}
