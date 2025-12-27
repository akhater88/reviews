<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetitionSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General settings
            [
                'key' => 'is_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Enable Competition',
                'label_ar' => 'تفعيل المسابقة',
                'is_public' => true,
            ],
            [
                'key' => 'winner_count',
                'value' => '10',
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Winner Count',
                'label_ar' => 'عدد الفائزين',
                'is_public' => true,
            ],
            [
                'key' => 'min_reviews',
                'value' => '10',
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Minimum Reviews Required',
                'label_ar' => 'الحد الأدنى للمراجعات',
                'is_public' => false,
            ],
            [
                'key' => 'sync_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Sync Frequency',
                'label_ar' => 'تكرار المزامنة',
                'is_public' => false,
            ],

            // Scoring weights
            [
                'key' => 'score_weight_rating',
                'value' => '25',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Rating Weight %',
                'label_ar' => 'وزن التقييم %',
                'is_public' => true,
            ],
            [
                'key' => 'score_weight_sentiment',
                'value' => '30',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Sentiment Weight %',
                'label_ar' => 'وزن المشاعر %',
                'is_public' => true,
            ],
            [
                'key' => 'score_weight_response',
                'value' => '15',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Response Rate Weight %',
                'label_ar' => 'وزن الاستجابة %',
                'is_public' => true,
            ],
            [
                'key' => 'score_weight_volume',
                'value' => '10',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Volume Weight %',
                'label_ar' => 'وزن الحجم %',
                'is_public' => true,
            ],
            [
                'key' => 'score_weight_trend',
                'value' => '10',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Trend Weight %',
                'label_ar' => 'وزن الاتجاه %',
                'is_public' => true,
            ],
            [
                'key' => 'score_weight_keywords',
                'value' => '10',
                'type' => 'integer',
                'group' => 'scoring',
                'label' => 'Keywords Weight %',
                'label_ar' => 'وزن الكلمات %',
                'is_public' => true,
            ],

            // Prizes
            [
                'key' => 'prize_1',
                'value' => '2000',
                'type' => 'integer',
                'group' => 'prizes',
                'label' => 'First Place Prize',
                'label_ar' => 'جائزة المركز الأول',
                'is_public' => true,
            ],
            [
                'key' => 'prize_2',
                'value' => '1500',
                'type' => 'integer',
                'group' => 'prizes',
                'label' => 'Second Place Prize',
                'label_ar' => 'جائزة المركز الثاني',
                'is_public' => true,
            ],
            [
                'key' => 'prize_3',
                'value' => '1000',
                'type' => 'integer',
                'group' => 'prizes',
                'label' => 'Third Place Prize',
                'label_ar' => 'جائزة المركز الثالث',
                'is_public' => true,
            ],
            [
                'key' => 'prize_others',
                'value' => '500',
                'type' => 'integer',
                'group' => 'prizes',
                'label' => 'Other Winners Prize',
                'label_ar' => 'جائزة باقي الفائزين',
                'is_public' => true,
            ],

            // Display settings
            [
                'key' => 'hero_title',
                'value' => 'مسابقة أفضل مطعم في السعودية',
                'type' => 'string',
                'group' => 'display',
                'label' => 'Hero Title',
                'label_ar' => 'العنوان الرئيسي',
                'is_public' => true,
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'رشّح مطعمك المفضل واربح إذا فاز!',
                'type' => 'string',
                'group' => 'display',
                'label' => 'Hero Subtitle',
                'label_ar' => 'العنوان الفرعي',
                'is_public' => true,
            ],
            [
                'key' => 'cta_button_text',
                'value' => 'رشّح الآن مجاناً',
                'type' => 'string',
                'group' => 'display',
                'label' => 'CTA Button Text',
                'label_ar' => 'نص زر المشاركة',
                'is_public' => true,
            ],
            [
                'key' => 'show_countdown',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'display',
                'label' => 'Show Countdown',
                'label_ar' => 'إظهار العد التنازلي',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('competition_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_at' => now()])
            );
        }
    }
}
