<?php

namespace App\Enums;

enum AnalysisType: string
{
    case SENTIMENT = 'sentiment';
    case RECOMMENDATIONS = 'recommendations';
    case KEYWORDS = 'keywords';
    case OPERATIONAL_INTELLIGENCE = 'operational_intelligence';
    case CATEGORY_INSIGHTS = 'category_insights';
    case EMPLOYEES_INSIGHTS = 'employees_insights';
    case GENDER_INSIGHTS = 'gender_insights';
    case OVERVIEW_CARDS = 'overview_cards';

    public function label(): string
    {
        return match ($this) {
            self::SENTIMENT => 'تحليل المشاعر',
            self::RECOMMENDATIONS => 'التوصيات',
            self::KEYWORDS => 'الكلمات المفتاحية',
            self::OPERATIONAL_INTELLIGENCE => 'الذكاء التشغيلي',
            self::CATEGORY_INSIGHTS => 'تحليل الفئات',
            self::EMPLOYEES_INSIGHTS => 'تحليل الموظفين',
            self::GENDER_INSIGHTS => 'تحليل الجنس',
            self::OVERVIEW_CARDS => 'بطاقات النظرة العامة',
        };
    }

    public function step(): int
    {
        return match ($this) {
            self::SENTIMENT => 2,
            self::RECOMMENDATIONS => 3,
            self::KEYWORDS => 4,
            self::OPERATIONAL_INTELLIGENCE => 5,
            self::CATEGORY_INSIGHTS => 6,
            self::EMPLOYEES_INSIGHTS => 7,
            self::GENDER_INSIGHTS => 8,
            self::OVERVIEW_CARDS => 9,
        };
    }

    public function progress(): int
    {
        return match ($this) {
            self::SENTIMENT => 20,
            self::RECOMMENDATIONS => 30,
            self::KEYWORDS => 40,
            self::OPERATIONAL_INTELLIGENCE => 50,
            self::CATEGORY_INSIGHTS => 60,
            self::EMPLOYEES_INSIGHTS => 70,
            self::GENDER_INSIGHTS => 80,
            self::OVERVIEW_CARDS => 100,
        };
    }
}
