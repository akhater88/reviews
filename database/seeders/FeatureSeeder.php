<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // Reviews
            ['key' => 'reviews_view', 'name' => 'View Reviews', 'name_ar' => 'عرض المراجعات', 'category' => 'reviews', 'sort_order' => 1],
            ['key' => 'reviews_filter', 'name' => 'Filter Reviews', 'name_ar' => 'فلترة المراجعات', 'category' => 'reviews', 'sort_order' => 2],
            ['key' => 'reviews_search', 'name' => 'Search Reviews', 'name_ar' => 'بحث المراجعات', 'category' => 'reviews', 'sort_order' => 3],

            // Analytics
            ['key' => 'branch_report', 'name' => 'Branch Report', 'name_ar' => 'تقرير الفرع', 'category' => 'analytics', 'sort_order' => 10],
            ['key' => 'monthly_rankings', 'name' => 'Monthly Rankings', 'name_ar' => 'المنافسة الشهرية', 'category' => 'analytics', 'sort_order' => 11],
            ['key' => 'competitor_analysis', 'name' => 'Competitor Analysis', 'name_ar' => 'تحليل المنافسين', 'category' => 'analytics', 'sort_order' => 12],
            ['key' => 'trend_analysis', 'name' => 'Trend Analysis', 'name_ar' => 'تحليل الاتجاهات', 'category' => 'analytics', 'sort_order' => 13],

            // AI
            ['key' => 'ai_reply', 'name' => 'AI Reply Generation', 'name_ar' => 'الرد الذكي', 'category' => 'ai', 'sort_order' => 20],
            ['key' => 'ai_sentiment', 'name' => 'AI Sentiment Analysis', 'name_ar' => 'تحليل المشاعر', 'category' => 'ai', 'sort_order' => 21],
            ['key' => 'ai_recommendations', 'name' => 'AI Recommendations', 'name_ar' => 'توصيات الذكاء الاصطناعي', 'category' => 'ai', 'sort_order' => 22],

            // Integration
            ['key' => 'google_publish', 'name' => 'Publish to Google', 'name_ar' => 'النشر على Google', 'category' => 'integration', 'sort_order' => 30],
            ['key' => 'google_sync', 'name' => 'Google Auto Sync', 'name_ar' => 'مزامنة تلقائية', 'category' => 'integration', 'sort_order' => 31],
            ['key' => 'api_access', 'name' => 'API Access', 'name_ar' => 'وصول API', 'category' => 'integration', 'sort_order' => 32],
            ['key' => 'webhook_notifications', 'name' => 'Webhook Notifications', 'name_ar' => 'إشعارات Webhook', 'category' => 'integration', 'sort_order' => 33],

            // Export
            ['key' => 'export_pdf', 'name' => 'Export to PDF', 'name_ar' => 'تصدير PDF', 'category' => 'export', 'sort_order' => 40],
            ['key' => 'export_excel', 'name' => 'Export to Excel', 'name_ar' => 'تصدير Excel', 'category' => 'export', 'sort_order' => 41],
            ['key' => 'scheduled_reports', 'name' => 'Scheduled Reports', 'name_ar' => 'تقارير مجدولة', 'category' => 'export', 'sort_order' => 42],

            // Support
            ['key' => 'email_support', 'name' => 'Email Support', 'name_ar' => 'دعم بريد إلكتروني', 'category' => 'support', 'sort_order' => 50],
            ['key' => 'priority_support', 'name' => 'Priority Support', 'name_ar' => 'دعم أولوية', 'category' => 'support', 'sort_order' => 51],
            ['key' => 'dedicated_manager', 'name' => 'Dedicated Account Manager', 'name_ar' => 'مدير حساب مخصص', 'category' => 'support', 'sort_order' => 52],
            ['key' => 'white_label', 'name' => 'White Label', 'name_ar' => 'العلامة البيضاء', 'category' => 'support', 'sort_order' => 53],
        ];

        foreach ($features as $featureData) {
            Feature::updateOrCreate(
                ['key' => $featureData['key']],
                $featureData
            );
        }

        // Assign features to plans
        $this->assignFeaturesToPlans();
    }

    private function assignFeaturesToPlans(): void
    {
        $planFeatures = [
            'free' => [
                'reviews_view', 'reviews_filter', 'reviews_search',
                'email_support',
            ],
            'starter' => [
                'reviews_view', 'reviews_filter', 'reviews_search',
                'branch_report',
                'ai_reply', 'ai_sentiment',
                'google_publish', 'google_sync',
                'email_support',
            ],
            'professional' => [
                'reviews_view', 'reviews_filter', 'reviews_search',
                'branch_report', 'monthly_rankings', 'competitor_analysis', 'trend_analysis',
                'ai_reply', 'ai_sentiment', 'ai_recommendations',
                'google_publish', 'google_sync',
                'export_pdf', 'export_excel', 'scheduled_reports',
                'email_support', 'priority_support',
            ],
            'enterprise' => [
                'reviews_view', 'reviews_filter', 'reviews_search',
                'branch_report', 'monthly_rankings', 'competitor_analysis', 'trend_analysis',
                'ai_reply', 'ai_sentiment', 'ai_recommendations',
                'google_publish', 'google_sync', 'api_access', 'webhook_notifications',
                'export_pdf', 'export_excel', 'scheduled_reports',
                'email_support', 'priority_support', 'dedicated_manager', 'white_label',
            ],
        ];

        foreach ($planFeatures as $planSlug => $featureKeys) {
            $plan = Plan::where('slug', $planSlug)->first();
            if (! $plan) {
                continue;
            }

            foreach ($featureKeys as $featureKey) {
                $feature = Feature::where('key', $featureKey)->first();
                if (! $feature) {
                    continue;
                }

                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_id' => $feature->id],
                    ['is_enabled' => true]
                );
            }
        }
    }
}
