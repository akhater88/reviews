<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'name_ar' => 'مجاني',
                'slug' => 'free',
                'description' => 'Perfect for trying out TABsense',
                'description_ar' => 'مثالي لتجربة المنصة',
                'price_monthly_sar' => 0,
                'price_yearly_sar' => 0,
                'price_monthly_usd' => 0,
                'price_yearly_usd' => 0,
                'is_free' => true,
                'sort_order' => 1,
                'color' => 'gray',
                'limits' => [
                    'max_branches' => 1,
                    'max_competitors' => 0,
                    'max_users' => 1,
                    'max_reviews_sync' => 100,
                    'max_ai_replies' => 10,
                    'max_ai_tokens' => 10000,
                    'max_api_calls' => 100,
                    'max_analysis_runs' => 2,
                    'analysis_retention_days' => 30,
                ],
            ],
            [
                'name' => 'Starter',
                'name_ar' => 'المبتدئ',
                'slug' => 'starter',
                'description' => 'Great for small restaurants',
                'description_ar' => 'رائع للمطاعم الصغيرة',
                'price_monthly_sar' => 199,
                'price_yearly_sar' => 1990,
                'price_monthly_usd' => 53,
                'price_yearly_usd' => 530,
                'sort_order' => 2,
                'color' => 'info',
                'limits' => [
                    'max_branches' => 3,
                    'max_competitors' => 2,
                    'max_users' => 3,
                    'max_reviews_sync' => 500,
                    'max_ai_replies' => 50,
                    'max_ai_tokens' => 50000,
                    'max_api_calls' => 500,
                    'max_analysis_runs' => 10,
                    'analysis_retention_days' => 90,
                ],
            ],
            [
                'name' => 'Professional',
                'name_ar' => 'الاحترافي',
                'slug' => 'professional',
                'description' => 'Best for growing chains',
                'description_ar' => 'الأفضل للسلاسل النامية',
                'price_monthly_sar' => 499,
                'price_yearly_sar' => 4990,
                'price_monthly_usd' => 133,
                'price_yearly_usd' => 1330,
                'is_popular' => true,
                'sort_order' => 3,
                'color' => 'primary',
                'limits' => [
                    'max_branches' => 10,
                    'max_competitors' => 5,
                    'max_users' => 10,
                    'max_reviews_sync' => 2000,
                    'max_ai_replies' => 200,
                    'max_ai_tokens' => 200000,
                    'max_api_calls' => 2000,
                    'max_analysis_runs' => 30,
                    'analysis_retention_days' => 365,
                ],
            ],
            [
                'name' => 'Enterprise',
                'name_ar' => 'المؤسسات',
                'slug' => 'enterprise',
                'description' => 'For large restaurant chains',
                'description_ar' => 'لسلاسل المطاعم الكبيرة',
                'price_monthly_sar' => 999,
                'price_yearly_sar' => 9990,
                'price_monthly_usd' => 266,
                'price_yearly_usd' => 2660,
                'sort_order' => 4,
                'color' => 'warning',
                'limits' => [
                    'max_branches' => -1, // Unlimited
                    'max_competitors' => 20,
                    'max_users' => -1,
                    'max_reviews_sync' => -1,
                    'max_ai_replies' => -1,
                    'max_ai_tokens' => -1,
                    'max_api_calls' => -1,
                    'max_analysis_runs' => -1,
                    'analysis_retention_days' => -1,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $limits = $planData['limits'];
            unset($planData['limits']);

            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            PlanLimit::updateOrCreate(
                ['plan_id' => $plan->id],
                $limits
            );
        }
    }
}
