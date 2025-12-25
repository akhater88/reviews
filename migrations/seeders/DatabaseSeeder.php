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
                'current_rating' => 4.8,
                'total_reviews' => 1247,
                'performance_score' => 94,
                'status' => 'excellent',
            ],
            [
                'name' => 'West Branch',
                'name_ar' => 'فرع الغرب الرئيسي',
                'city' => 'جدة',
                'country' => 'المملكة العربية السعودية',
                'current_rating' => 4.6,
                'total_reviews' => 892,
                'performance_score' => 89,
                'status' => 'excellent',
            ],
            [
                'name' => 'Commercial District',
                'name_ar' => 'فرع المنطقة التجارية',
                'city' => 'الدمام',
                'country' => 'المملكة العربية السعودية',
                'current_rating' => 4.4,
                'total_reviews' => 656,
                'performance_score' => 85,
                'status' => 'good',
            ],
            [
                'name' => 'Corniche Branch',
                'name_ar' => 'فرع الكورنيش',
                'city' => 'أبوظبي',
                'country' => 'الإمارات العربية المتحدة',
                'current_rating' => 4.9,
                'total_reviews' => 743,
                'performance_score' => 91,
                'status' => 'excellent',
            ],
            [
                'name' => 'Northern District',
                'name_ar' => 'فرع المنطقة الشمالية',
                'city' => 'الكويت',
                'country' => 'دولة الكويت',
                'current_rating' => 4.6,
                'total_reviews' => 445,
                'performance_score' => 87,
                'status' => 'good',
            ],
        ];

        foreach ($branches as $branchData) {
            $branch = Branch::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                ...$branchData,
                'is_active' => true,
            ]);

            // Create sample reviews for each branch
            $this->createSampleReviews($branch);
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

    private function createSampleReviews(Branch $branch): void
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
            ],
            [
                'reviewer_name' => 'فاطمة علي',
                'rating' => 4,
                'text' => 'الخدمة جيدة بشكل عام، وقت الانتظار معقول. يمكن تحسين بعض التفاصيل الصغيرة.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.70,
                'categories' => ['service', 'speed'],
                'reviewer_gender' => 'female',
            ],
            [
                'reviewer_name' => 'محمد السعيد',
                'rating' => 3,
                'text' => 'الأكل جيد لكن الخدمة بطيئة للغاية لمحاولة الاستعجال. عند الدفع يتطلب منك أنت تدفع من الباركود في لذلك لازم تعطيهم بخسيس أو تعطيهم أنه الخدمة سيئة لا انصح بـ هذا المطعم إلا إذا غيرو طريقة الدفع.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.45,
                'categories' => ['service', 'speed', 'price'],
                'reviewer_gender' => 'male',
            ],
            [
                'reviewer_name' => 'سارة يوسف',
                'rating' => 5,
                'text' => 'مطعم يستحق الإشادة والأكل جدا رائع والخدمة ممتازة. المطعم رائع جدا وكل شي حلو لكن للاسف زاد السعر وقلت الكمية وهالشي مزعج حدا، لكن تجربة جميلة',
                'sentiment' => 'positive',
                'sentiment_score' => 0.75,
                'categories' => ['food', 'service', 'price'],
                'reviewer_gender' => 'female',
            ],
            [
                'reviewer_name' => 'خالد العتيبي',
                'rating' => 2,
                'text' => 'الأكل عادي جداً، الأسعار مرتفعة مقارنة بالجودة. لن أعود مرة أخرى.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.65,
                'categories' => ['food', 'price', 'quality'],
                'reviewer_gender' => 'male',
            ],
        ];

        foreach ($reviews as $reviewData) {
            Review::create([
                'branch_id' => $branch->id,
                'google_review_id' => 'demo_' . uniqid(),
                'review_date' => now()->subDays(rand(1, 30)),
                'needs_reply' => $reviewData['rating'] <= 3,
                'is_replied' => $reviewData['rating'] > 3 && rand(0, 1),
                ...$reviewData,
            ]);
        }
    }
}
