<?php

namespace App\Enums;

enum AnalysisStep: string
{
    case FETCH_RESTAURANT_INFO = 'fetch_restaurant_info';
    case ANALYZE_SENTIMENT = 'analyze_sentiment';
    case GENERATE_RECOMMENDATIONS = 'generate_recommendations';
    case EXTRACT_KEYWORDS = 'extract_keywords';
    case GENERATE_OPERATIONAL_INTELLIGENCE = 'generate_operational_intelligence';
    case ANALYZE_CATEGORIES = 'analyze_categories';
    case ANALYZE_EMPLOYEES = 'analyze_employees';
    case GENDER_INSIGHTS = 'gender_insights';
    case CREATE_OVERVIEW_CARDS = 'create_overview_cards';

    public function label(): string
    {
        return match ($this) {
            self::FETCH_RESTAURANT_INFO => 'جلب معلومات المطعم',
            self::ANALYZE_SENTIMENT => 'تحليل المشاعر',
            self::GENERATE_RECOMMENDATIONS => 'إنشاء التوصيات',
            self::EXTRACT_KEYWORDS => 'استخراج الكلمات المفتاحية',
            self::GENERATE_OPERATIONAL_INTELLIGENCE => 'الذكاء التشغيلي',
            self::ANALYZE_CATEGORIES => 'تحليل الفئات',
            self::ANALYZE_EMPLOYEES => 'تحليل الموظفين',
            self::GENDER_INSIGHTS => 'تحليل الجنس',
            self::CREATE_OVERVIEW_CARDS => 'إنشاء البطاقات',
        };
    }

    public function progress(): int
    {
        return match ($this) {
            self::FETCH_RESTAURANT_INFO => 10,
            self::ANALYZE_SENTIMENT => 20,
            self::GENERATE_RECOMMENDATIONS => 30,
            self::EXTRACT_KEYWORDS => 40,
            self::GENERATE_OPERATIONAL_INTELLIGENCE => 50,
            self::ANALYZE_CATEGORIES => 60,
            self::ANALYZE_EMPLOYEES => 70,
            self::GENDER_INSIGHTS => 80,
            self::CREATE_OVERVIEW_CARDS => 100,
        };
    }
}
