<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Review;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo tenant
        $tenant = Tenant::create([
            'name' => 'Taboor Restaurant',
            'name_ar' => 'مطعم طابور',
            'slug' => 'taboor',
            'email' => 'info@taboor.com',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        // Create admin user
        $admin = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'مدير النظام',
            'email' => 'admin@taboor.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create sample branches
        $branches = [
            [
                'name' => 'Downtown Branch',
                'name_ar' => 'فرع وسط المدينة',
                'city' => 'الرياض',
                'country' => 'المملكة العربية السعودية',
                'google_place_id' => 'ChIJN1t_tDeuEmsRUsoyG83frY4',
                'current_rating' => 4.8,
                'total_reviews' => 1247,
                'performance_score' => 94,
                'status' => 'excellent',
                'source' => 'manual',
                'branch_type' => 'owned',
                'sync_status' => 'pending',
            ],
            [
                'name' => 'West Branch',
                'name_ar' => 'فرع الغرب الرئيسي',
                'city' => 'جدة',
                'country' => 'المملكة العربية السعودية',
                'google_place_id' => 'ChIJVVVVVVVVVVVRXXXXXXXXXXX',
                'current_rating' => 4.6,
                'total_reviews' => 892,
                'performance_score' => 89,
                'status' => 'excellent',
                'source' => 'manual',
                'branch_type' => 'owned',
                'sync_status' => 'pending',
            ],
            [
                'name' => 'Commercial District',
                'name_ar' => 'فرع المنطقة التجارية',
                'city' => 'الدمام',
                'country' => 'المملكة العربية السعودية',
                'google_place_id' => 'ChIJWWWWWWWWWWWRYYYYYYYYYYY',
                'current_rating' => 4.4,
                'total_reviews' => 656,
                'performance_score' => 85,
                'status' => 'good',
                'source' => 'manual',
                'branch_type' => 'owned',
                'sync_status' => 'pending',
            ],
            [
                'name' => 'Corniche Branch',
                'name_ar' => 'فرع الكورنيش',
                'city' => 'أبوظبي',
                'country' => 'الإمارات العربية المتحدة',
                'google_place_id' => 'ChIJXXXXXXXXXXXRZZZZZZZZZZZ',
                'current_rating' => 4.9,
                'total_reviews' => 743,
                'performance_score' => 91,
                'status' => 'excellent',
                'source' => 'manual',
                'branch_type' => 'owned',
                'sync_status' => 'pending',
            ],
            [
                'name' => 'Northern District',
                'name_ar' => 'فرع المنطقة الشمالية',
                'city' => 'الكويت',
                'country' => 'دولة الكويت',
                'google_place_id' => 'ChIJYYYYYYYYYYYRAAAAAAAAAAAA',
                'current_rating' => 4.6,
                'total_reviews' => 445,
                'performance_score' => 87,
                'status' => 'good',
                'source' => 'manual',
                'branch_type' => 'owned',
                'sync_status' => 'pending',
            ],
        ];

        foreach ($branches as $branchData) {
            $branch = Branch::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                ...$branchData,
                'is_active' => true,
            ]);

            // Create sample reviews for each branch
            $this->createSampleReviews($branch, $tenant);
        }

        // Create a branch manager
        $manager = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'أحمد محمد',
            'email' => 'manager@taboor.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Assign manager to first two branches
        $manager->branches()->attach([1, 2]);

        $this->command->info('Demo data created successfully!');
        $this->command->info('Admin login: admin@taboor.com / password');
        $this->command->info('Manager login: manager@taboor.com / password');
    }

    private function createSampleReviews(Branch $branch, Tenant $tenant): void
    {
        $reviews = [
            [
                'reviewer_name' => 'أحمد محمد',
                'rating' => 5,
                'text' => 'تجربة رائعة! الموظفون محترفون جداً والخدمة ممتازة. المكان نظيف ومريح. أنصح بشدة بزيارة هذا الفرع.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.95,
                'categories' => ['service', 'staff', 'cleanliness'],
                'reviewer_gender' => 'male',
                'owner_reply' => 'شكراً لك أخي أحمد على كلماتك الطيبة! سعداء بتجربتك الرائعة ونتطلع لخدمتك دائماً.',
                'owner_reply_date' => now()->subDays(rand(1, 5)),
            ],
            [
                'reviewer_name' => 'فاطمة علي',
                'rating' => 4,
                'text' => 'الخدمة جيدة بشكل عام، وقت الانتظار معقول. يمكن تحسين بعض التفاصيل الصغيرة.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.70,
                'categories' => ['service', 'speed'],
                'reviewer_gender' => 'female',
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'محمد السعيد',
                'rating' => 3,
                'text' => 'الأكل جيد لكن الخدمة بطيئة للغاية. عند الدفع يتطلب منك الدفع من الباركود.',
                'sentiment' => 'neutral',
                'sentiment_score' => -0.15,
                'categories' => ['service', 'speed', 'price'],
                'reviewer_gender' => 'male',
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'سارة يوسف',
                'rating' => 5,
                'text' => 'مطعم يستحق الإشادة والأكل جداً رائع والخدمة ممتازة. المطعم رائع جداً وكل شيء حلو!',
                'sentiment' => 'positive',
                'sentiment_score' => 0.90,
                'categories' => ['food', 'service'],
                'reviewer_gender' => 'female',
                'owner_reply' => 'شكراً جزيلاً سارة! نسعد دائماً بزيارتكم.',
                'owner_reply_date' => now()->subDays(rand(1, 3)),
            ],
            [
                'reviewer_name' => 'خالد العتيبي',
                'rating' => 2,
                'text' => 'الأكل عادي جداً، الأسعار مرتفعة مقارنة بالجودة. لن أعود مرة أخرى.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.65,
                'categories' => ['food', 'price', 'quality'],
                'reviewer_gender' => 'male',
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'نورة الشمري',
                'rating' => 5,
                'text' => null, // Star-only review
                'sentiment' => 'positive',
                'sentiment_score' => 0.80,
                'categories' => null,
                'reviewer_gender' => 'female',
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'عبدالله القحطاني',
                'rating' => 1,
                'text' => 'أسوأ تجربة! الطعام بارد والخدمة سيئة جداً. لا أنصح أبداً.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.95,
                'categories' => ['food', 'service', 'quality'],
                'reviewer_gender' => 'male',
                'owner_reply' => 'نأسف جداً لتجربتك السيئة أخي عبدالله. نود التواصل معك لحل المشكلة. يرجى مراسلتنا.',
                'owner_reply_date' => now()->subDays(1),
            ],
        ];

        foreach ($reviews as $index => $reviewData) {
            $reviewDate = now()->subDays(rand(1, 60));
            $hasReply = !empty($reviewData['owner_reply']);

            Review::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,

                // External IDs
                'google_review_id' => null,
                'outscraper_review_id' => 'demo_' . $branch->id . '_' . $index . '_' . uniqid(),

                // Author info
                'reviewer_name' => $reviewData['reviewer_name'],
                'reviewer_photo_url' => null,
                'author_url' => null,

                // Content
                'rating' => $reviewData['rating'],
                'text' => $reviewData['text'],
                'language' => 'ar',

                // Dates
                'review_date' => $reviewDate,
                'collected_at' => now(),

                // Source
                'source' => 'outscraper',

                // Owner reply
                'owner_reply' => $reviewData['owner_reply'],
                'owner_reply_date' => $reviewData['owner_reply_date'],
                'replied_via_tabsense' => false,

                // AI Analysis
                'sentiment' => $reviewData['sentiment'],
                'sentiment_score' => $reviewData['sentiment_score'],
                'ai_summary' => null,
                'categories' => $reviewData['categories'],
                'keywords' => null,
                'reviewer_gender' => $reviewData['reviewer_gender'],

                // Quality
                'quality_score' => $this->calculateQualityScore($reviewData['text']),
                'is_spam' => false,
                'is_hidden' => false,

                // Reply status
                'is_replied' => $hasReply,
                'needs_reply' => !$hasReply && $reviewData['rating'] <= 3,

                // Metadata
                'metadata' => [
                    'review_likes' => rand(0, 15),
                    'demo' => true,
                ],
            ]);
        }
    }

    private function calculateQualityScore(?string $text): float
    {
        if (empty($text)) {
            return 0.50;
        }

        $length = mb_strlen($text);

        if ($length > 200) return 1.00;
        if ($length > 100) return 0.90;
        if ($length > 50) return 0.80;
        if ($length > 20) return 0.70;

        return 0.60;
    }
}
