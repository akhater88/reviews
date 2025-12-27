<?php

namespace App\Enums;

enum ReplyTone: string
{
    case PROFESSIONAL = 'professional';
    case FRIENDLY = 'friendly';
    case APOLOGETIC = 'apologetic';

    public function label(): string
    {
        return match($this) {
            self::PROFESSIONAL => 'مهني',
            self::FRIENDLY => 'ودي',
            self::APOLOGETIC => 'اعتذاري',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PROFESSIONAL => 'رد رسمي ومهني مناسب لجميع المراجعات',
            self::FRIENDLY => 'رد ودود ودافئ مع إيموجي للمراجعات الإيجابية',
            self::APOLOGETIC => 'رد اعتذاري ومتفهم للمراجعات السلبية',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PROFESSIONAL => 'heroicon-o-briefcase',
            self::FRIENDLY => 'heroicon-o-face-smile',
            self::APOLOGETIC => 'heroicon-o-heart',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PROFESSIONAL => 'primary',
            self::FRIENDLY => 'success',
            self::APOLOGETIC => 'warning',
        };
    }
}
