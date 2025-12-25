<?php

namespace App\Enums;

enum SyncStatus: string
{
    case PENDING = 'pending';
    case SYNCING = 'syncing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'في الانتظار',
            self::SYNCING => 'جاري المزامنة',
            self::COMPLETED => 'مكتمل',
            self::FAILED => 'فشل',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::SYNCING => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::SYNCING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }
}
