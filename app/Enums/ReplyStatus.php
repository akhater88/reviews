<?php

namespace App\Enums;

enum ReplyStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'مسودة',
            self::PUBLISHING => 'جاري النشر',
            self::PUBLISHED => 'تم النشر',
            self::FAILED => 'فشل النشر',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'heroicon-o-document',
            self::PUBLISHING => 'heroicon-o-clock',
            self::PUBLISHED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PUBLISHING => 'warning',
            self::PUBLISHED => 'success',
            self::FAILED => 'danger',
        };
    }
}
