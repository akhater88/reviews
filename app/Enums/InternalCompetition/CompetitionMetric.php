<?php

namespace App\Enums\InternalCompetition;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum CompetitionMetric: string implements HasLabel, HasColor, HasIcon
{
    case EMPLOYEE_MENTIONS = 'employee_mentions';
    case CUSTOMER_SATISFACTION = 'customer_satisfaction';
    case RESPONSE_TIME = 'response_time';
    case FOOD_TASTE = 'food_taste';

    public function getLabel(): string
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => 'أفضل موظف',
            self::CUSTOMER_SATISFACTION => 'رضا العملاء',
            self::RESPONSE_TIME => 'سرعة الاستجابة',
            self::FOOD_TASTE => 'الطعام/الطعم',
        };
    }

    public function getColor(): string | array | null
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => 'success',
            self::CUSTOMER_SATISFACTION => 'warning',
            self::RESPONSE_TIME => 'info',
            self::FOOD_TASTE => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => 'heroicon-o-user-circle',
            self::CUSTOMER_SATISFACTION => 'heroicon-o-star',
            self::RESPONSE_TIME => 'heroicon-o-clock',
            self::FOOD_TASTE => 'heroicon-o-cake',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => 'الموظف الأكثر ذكراً بشكل إيجابي في المراجعات',
            self::CUSTOMER_SATISFACTION => 'الفرع ذو أعلى تقييم ورضا عملاء',
            self::RESPONSE_TIME => 'الفرع ذو أسرع وقت استجابة للمراجعات',
            self::FOOD_TASTE => 'الفرع ذو أعلى تقييمات إيجابية للطعام والطعم',
        };
    }

    public function winnerType(): string
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => 'employee',
            self::CUSTOMER_SATISFACTION => 'branch',
            self::RESPONSE_TIME => 'branch',
            self::FOOD_TASTE => 'branch',
        };
    }

    public function scoringFormula(): string
    {
        return match($this) {
            self::EMPLOYEE_MENTIONS => '(إيجابي × 10) + (محايد × 1) - (سلبي × 5)',
            self::CUSTOMER_SATISFACTION => '(متوسط التقييم × 20) + (نسبة الإيجابية × 0.5)',
            self::RESPONSE_TIME => '100 - (متوسط ساعات الرد × 2)',
            self::FOOD_TASTE => '(إيجابي × 10) - (سلبي × 5)',
        };
    }
}
