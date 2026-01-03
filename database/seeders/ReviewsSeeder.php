<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReviewsSeeder extends Seeder
{
    /**
     * Branch IDs to seed reviews for
     */
    private array $branchIds = [6, 8, 9, 10];

    /**
     * Employee names for mentions in reviews
     */
    private array $employeeNames = [
        'محمد', 'أحمد', 'خالد', 'فهد', 'سعود',
        'عبدالله', 'يوسف', 'علي', 'حسن', 'عمر',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::withoutGlobalScopes()
            ->whereIn('id', $this->branchIds)
            ->get();

        if ($branches->isEmpty()) {
            $this->command->error('No branches found with IDs: ' . implode(', ', $this->branchIds));
            return;
        }

        $this->command->info('Creating reviews for ' . $branches->count() . ' branches...');

        foreach ($branches as $branch) {
            $this->createReviewsForBranch($branch);
            $this->command->info("  - Created reviews for branch: {$branch->name} (ID: {$branch->id})");
        }

        $this->command->info('Reviews seeder completed successfully!');
    }

    /**
     * Create reviews for a single branch
     */
    private function createReviewsForBranch(Branch $branch): void
    {
        $reviewsData = $this->getReviewsData($branch->id);

        foreach ($reviewsData as $index => $reviewData) {
            $hasReply = !empty($reviewData['owner_reply']);

            Review::withoutGlobalScopes()->create([
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,

                // External IDs
                'google_review_id' => null,
                'outscraper_review_id' => 'seed_' . $branch->id . '_' . $index . '_' . uniqid(),

                // Author info
                'reviewer_name' => $reviewData['reviewer_name'],
                'reviewer_photo_url' => null,
                'author_url' => null,

                // Content
                'rating' => $reviewData['rating'],
                'text' => $reviewData['text'],
                'language' => 'ar',

                // Dates
                'review_date' => $reviewData['review_date'],
                'collected_at' => now(),

                // Source
                'source' => 'outscraper',

                // Owner reply
                'owner_reply' => $reviewData['owner_reply'] ?? null,
                'owner_reply_date' => $reviewData['owner_reply_date'] ?? null,
                'replied_via_tabsense' => false,

                // AI Analysis
                'sentiment' => $reviewData['sentiment'],
                'sentiment_score' => $reviewData['sentiment_score'],
                'ai_summary' => null,
                'categories' => $reviewData['categories'],
                'keywords' => $reviewData['keywords'] ?? null,
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
                    'review_likes' => rand(0, 25),
                    'seeded' => true,
                    'seeder' => 'ReviewsSeeder',
                ],
            ]);
        }
    }

    /**
     * Get reviews data for a branch - varied by branch for competition differentiation
     */
    private function getReviewsData(int $branchId): array
    {
        $baseReviews = $this->getBaseReviews();
        $branchSpecificReviews = $this->getBranchSpecificReviews($branchId);

        return array_merge($baseReviews, $branchSpecificReviews);
    }

    /**
     * Common reviews for all branches
     */
    private function getBaseReviews(): array
    {
        return [
            // Day 1-5: Recent reviews
            [
                'reviewer_name' => 'سلمان العتيبي',
                'rating' => 5,
                'text' => 'الطعام لذيذ جداً والطعم ممتاز! الأخ ' . $this->getRandomEmployee() . ' كان رائعاً في الخدمة وساعدنا في اختيار الأطباق المناسبة. تجربة لا تنسى!',
                'sentiment' => 'positive',
                'sentiment_score' => 0.95,
                'categories' => ['food', 'taste', 'service', 'staff'],
                'keywords' => ['طعام', 'لذيذ', 'طعم', 'ممتاز', 'خدمة'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(1),
                'owner_reply' => 'شكراً جزيلاً أخي سلمان! نفتخر بخدمتك دائماً.',
                'owner_reply_date' => Carbon::now()->subHours(12),
            ],
            [
                'reviewer_name' => 'هند المطيري',
                'rating' => 4,
                'text' => 'الأكل طيب والطعم جميل. الخدمة سريعة والمكان نظيف. سأعود مرة أخرى بالتأكيد.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.80,
                'categories' => ['food', 'taste', 'service', 'cleanliness', 'speed'],
                'keywords' => ['أكل', 'طيب', 'طعم', 'نظيف', 'سريعة'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(2),
                'owner_reply' => 'شكراً هند! ننتظر زيارتك القادمة.',
                'owner_reply_date' => Carbon::now()->subDays(1),
            ],
            [
                'reviewer_name' => 'فيصل الدوسري',
                'rating' => 3,
                'text' => 'الطعم مقبول لكن الانتظار طويل. الموظف ' . $this->getRandomEmployee() . ' كان ودوداً لكن الخدمة بطيئة.',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.10,
                'categories' => ['taste', 'speed', 'staff', 'service'],
                'keywords' => ['طعم', 'مقبول', 'انتظار', 'بطيئة'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(3),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],

            // Day 6-10: More reviews
            [
                'reviewer_name' => 'نوف السبيعي',
                'rating' => 5,
                'text' => 'أفضل مطعم جربته! الطعم رائع والأكل شهي. الأخ ' . $this->getRandomEmployee() . ' قدم خدمة استثنائية.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.98,
                'categories' => ['food', 'taste', 'staff', 'service'],
                'keywords' => ['أفضل', 'مطعم', 'طعم', 'رائع', 'شهي'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(6),
                'owner_reply' => 'شكراً نوف! سعداء بتجربتك الرائعة.',
                'owner_reply_date' => Carbon::now()->subDays(5),
            ],
            [
                'reviewer_name' => 'تركي الحربي',
                'rating' => 2,
                'text' => 'للأسف الطعام بارد والطعم غير مرضي. الأسعار مرتفعة مقارنة بالجودة.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.70,
                'categories' => ['food', 'taste', 'price', 'quality'],
                'keywords' => ['بارد', 'طعم', 'غير مرضي', 'أسعار', 'مرتفعة'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(7),
                'owner_reply' => 'نعتذر عن تجربتك أخي تركي. نتواصل معك لحل المشكلة.',
                'owner_reply_date' => Carbon::now()->subDays(6),
            ],
            [
                'reviewer_name' => 'ريم القحطاني',
                'rating' => 4,
                'text' => 'الطعم جيد جداً والمكان مريح. أنصح به للعائلات.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.75,
                'categories' => ['taste', 'ambiance'],
                'keywords' => ['طعم', 'جيد', 'مريح', 'عائلات'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(8),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],

            // Day 11-20: Historical reviews
            [
                'reviewer_name' => 'عبدالرحمن الشهري',
                'rating' => 5,
                'text' => 'تجربة ممتازة! الطعام لذيذ جداً والطعم أصيل. الموظف ' . $this->getRandomEmployee() . ' محترف ومتعاون.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.92,
                'categories' => ['food', 'taste', 'staff', 'service'],
                'keywords' => ['ممتازة', 'طعام', 'لذيذ', 'طعم', 'أصيل'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(12),
                'owner_reply' => 'شكراً عبدالرحمن! نفخر بخدمتك.',
                'owner_reply_date' => Carbon::now()->subDays(11),
            ],
            [
                'reviewer_name' => 'لمياء العنزي',
                'rating' => 1,
                'text' => 'أسوأ تجربة! الطعام سيء والطعم غريب. لن أعود مرة أخرى.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.95,
                'categories' => ['food', 'taste', 'quality'],
                'keywords' => ['سيء', 'طعم', 'غريب'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(15),
                'owner_reply' => 'نأسف جداً لتجربتك. نود معرفة المزيد لتحسين خدماتنا.',
                'owner_reply_date' => Carbon::now()->subDays(14),
            ],
            [
                'reviewer_name' => 'ماجد السعيد',
                'rating' => 4,
                'text' => 'الطعم ممتاز والخدمة جيدة. الأسعار معقولة.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.78,
                'categories' => ['taste', 'service', 'price'],
                'keywords' => ['طعم', 'ممتاز', 'خدمة', 'أسعار'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(18),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],

            // Day 21-35: More historical reviews
            [
                'reviewer_name' => 'غادة المالكي',
                'rating' => 5,
                'text' => 'الطعام شهي والطعم رائع! الأخ ' . $this->getRandomEmployee() . ' كان مميزاً في الاستقبال والخدمة.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.90,
                'categories' => ['food', 'taste', 'staff', 'service'],
                'keywords' => ['شهي', 'طعم', 'رائع', 'مميز'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(22),
                'owner_reply' => 'شكراً غادة! سعداء بزيارتك.',
                'owner_reply_date' => Carbon::now()->subDays(21),
            ],
            [
                'reviewer_name' => 'بندر الزهراني',
                'rating' => 3,
                'text' => 'الطعم عادي. الخدمة جيدة لكن يحتاج تحسين في نوعية الطعام.',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.05,
                'categories' => ['taste', 'service', 'food', 'quality'],
                'keywords' => ['طعم', 'عادي', 'تحسين'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(25),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'أريج الغامدي',
                'rating' => 5,
                'text' => null, // Star-only review
                'sentiment' => 'positive',
                'sentiment_score' => 0.80,
                'categories' => null,
                'keywords' => null,
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(28),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'عادل البيشي',
                'rating' => 4,
                'text' => 'الطعم طيب والأكل لذيذ. الموظف ' . $this->getRandomEmployee() . ' ساعدنا كثيراً.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.82,
                'categories' => ['taste', 'food', 'staff'],
                'keywords' => ['طعم', 'طيب', 'أكل', 'لذيذ'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(30),
                'owner_reply' => 'شكراً عادل!',
                'owner_reply_date' => Carbon::now()->subDays(29),
            ],

            // Day 36-50: Older reviews
            [
                'reviewer_name' => 'منى الشريف',
                'rating' => 2,
                'text' => 'الطعم ليس كما توقعت. الانتظار كان طويلاً جداً.',
                'sentiment' => 'negative',
                'sentiment_score' => -0.55,
                'categories' => ['taste', 'speed'],
                'keywords' => ['طعم', 'انتظار', 'طويل'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(38),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
            [
                'reviewer_name' => 'ياسر العمري',
                'rating' => 5,
                'text' => 'أفضل طعم في المنطقة! الطعام ممتاز والخدمة راقية. الأخ ' . $this->getRandomEmployee() . ' محترف.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.96,
                'categories' => ['taste', 'food', 'service', 'staff'],
                'keywords' => ['أفضل', 'طعم', 'ممتاز', 'راقية'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(42),
                'owner_reply' => 'شكراً ياسر! نفتخر بثقتك.',
                'owner_reply_date' => Carbon::now()->subDays(41),
            ],
            [
                'reviewer_name' => 'سمر الحمود',
                'rating' => 4,
                'text' => 'الطعم جيد والمكان نظيف. أنصح بتجربة الأطباق الخاصة.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.72,
                'categories' => ['taste', 'cleanliness'],
                'keywords' => ['طعم', 'جيد', 'نظيف'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(45),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],

            // Day 51-60: Oldest reviews
            [
                'reviewer_name' => 'نايف العسيري',
                'rating' => 3,
                'text' => 'الطعم متوسط. الخدمة جيدة لكن الطعام يحتاج تحسين.',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.00,
                'categories' => ['taste', 'service', 'food'],
                'keywords' => ['طعم', 'متوسط', 'تحسين'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(52),
                'owner_reply' => 'شكراً على ملاحظاتك نايف. نعمل على التحسين.',
                'owner_reply_date' => Carbon::now()->subDays(51),
            ],
            [
                'reviewer_name' => 'دلال الراشد',
                'rating' => 5,
                'text' => 'طعم رائع وخدمة ممتازة! الموظف ' . $this->getRandomEmployee() . ' كان مذهلاً.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.94,
                'categories' => ['taste', 'service', 'staff'],
                'keywords' => ['طعم', 'رائع', 'ممتازة', 'مذهل'],
                'reviewer_gender' => 'female',
                'review_date' => Carbon::now()->subDays(55),
                'owner_reply' => 'شكراً دلال!',
                'owner_reply_date' => Carbon::now()->subDays(54),
            ],
            [
                'reviewer_name' => 'حمد الهاجري',
                'rating' => 4,
                'text' => 'أكل لذيذ وطعم مميز. سأعود قريباً.',
                'sentiment' => 'positive',
                'sentiment_score' => 0.85,
                'categories' => ['food', 'taste'],
                'keywords' => ['أكل', 'لذيذ', 'طعم', 'مميز'],
                'reviewer_gender' => 'male',
                'review_date' => Carbon::now()->subDays(58),
                'owner_reply' => null,
                'owner_reply_date' => null,
            ],
        ];
    }

    /**
     * Get branch-specific reviews to differentiate competition scores
     */
    private function getBranchSpecificReviews(int $branchId): array
    {
        return match($branchId) {
            // Branch 6: High food/taste scores, moderate employee mentions
            6 => [
                [
                    'reviewer_name' => 'سعد الأحمري',
                    'rating' => 5,
                    'text' => 'الطعم أسطوري! أفضل طعام جربته في حياتي. النكهات متناسقة والتقديم رائع.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.99,
                    'categories' => ['taste', 'food', 'quality'],
                    'keywords' => ['طعم', 'أسطوري', 'أفضل', 'طعام', 'نكهات'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(4),
                    'owner_reply' => 'شكراً سعد! سعداء بإعجابك.',
                    'owner_reply_date' => Carbon::now()->subDays(3),
                ],
                [
                    'reviewer_name' => 'مها الخالدي',
                    'rating' => 5,
                    'text' => 'الطعم لا يوصف! كل شيء طازج ولذيذ. الطعام يستحق كل ريال.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.97,
                    'categories' => ['taste', 'food', 'price', 'quality'],
                    'keywords' => ['طعم', 'طازج', 'لذيذ', 'طعام'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(9),
                    'owner_reply' => 'شكراً مها!',
                    'owner_reply_date' => Carbon::now()->subDays(8),
                ],
                [
                    'reviewer_name' => 'صالح النعيمي',
                    'rating' => 5,
                    'text' => 'أفضل طعم في المملكة! الأطباق مميزة والنكهات أصيلة.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.96,
                    'categories' => ['taste', 'food', 'quality'],
                    'keywords' => ['أفضل', 'طعم', 'مميزة', 'نكهات', 'أصيلة'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(14),
                    'owner_reply' => null,
                    'owner_reply_date' => null,
                ],
                [
                    'reviewer_name' => 'وفاء البلوي',
                    'rating' => 4,
                    'text' => 'الطعم جداً لذيذ! الأخ محمد كان رائعاً في الخدمة.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.88,
                    'categories' => ['taste', 'staff', 'service'],
                    'keywords' => ['طعم', 'لذيذ', 'رائع'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(20),
                    'owner_reply' => 'شكراً وفاء! سننقل تحياتك لمحمد.',
                    'owner_reply_date' => Carbon::now()->subDays(19),
                ],
            ],

            // Branch 8: High employee mentions, good response time
            8 => [
                [
                    'reviewer_name' => 'فهد المحمد',
                    'rating' => 5,
                    'text' => 'الموظف أحمد كان استثنائياً! خدمة مميزة ومحترفة. شكراً أحمد على كل شيء.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.95,
                    'categories' => ['staff', 'service'],
                    'keywords' => ['أحمد', 'استثنائي', 'خدمة', 'مميزة'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(2),
                    'owner_reply' => 'شكراً فهد! سننقل تحياتك لأحمد.',
                    'owner_reply_date' => Carbon::now()->subDays(1)->subHours(2),
                ],
                [
                    'reviewer_name' => 'عبير السالم',
                    'rating' => 5,
                    'text' => 'الأخ خالد موظف محترم ومتعاون جداً. ساعدنا في كل شيء. شكراً خالد!',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.93,
                    'categories' => ['staff', 'service'],
                    'keywords' => ['خالد', 'محترم', 'متعاون'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(5),
                    'owner_reply' => 'شكراً عبير! خالد يشكرك على كلماتك الطيبة.',
                    'owner_reply_date' => Carbon::now()->subDays(4)->subHours(4),
                ],
                [
                    'reviewer_name' => 'محمد العتيق',
                    'rating' => 5,
                    'text' => 'الموظفين ممتازين! بالأخص الأخ فهد والأخ سعود. خدمة راقية جداً.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.94,
                    'categories' => ['staff', 'service'],
                    'keywords' => ['فهد', 'سعود', 'موظفين', 'ممتازين', 'راقية'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(10),
                    'owner_reply' => 'شكراً محمد على كلماتك!',
                    'owner_reply_date' => Carbon::now()->subDays(9)->subHours(6),
                ],
                [
                    'reviewer_name' => 'سارة الفيصل',
                    'rating' => 4,
                    'text' => 'الأخ عبدالله كان مميزاً في الاستقبال. الطعم جيد والخدمة ممتازة.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.85,
                    'categories' => ['staff', 'taste', 'service'],
                    'keywords' => ['عبدالله', 'مميز', 'طعم', 'جيد'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(16),
                    'owner_reply' => 'شكراً سارة!',
                    'owner_reply_date' => Carbon::now()->subDays(15)->subHours(3),
                ],
            ],

            // Branch 9: High customer satisfaction, varied reviews
            9 => [
                [
                    'reviewer_name' => 'عمر الصالح',
                    'rating' => 5,
                    'text' => 'تجربة لا تنسى! كل شيء كان مثالياً. الطعم رائع والخدمة ممتازة والمكان نظيف.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.98,
                    'categories' => ['taste', 'service', 'cleanliness', 'ambiance'],
                    'keywords' => ['مثالي', 'طعم', 'رائع', 'ممتازة', 'نظيف'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(1),
                    'owner_reply' => 'شكراً عمر! سعداء بتجربتك المميزة.',
                    'owner_reply_date' => Carbon::now()->subHours(8),
                ],
                [
                    'reviewer_name' => 'نورة الحسين',
                    'rating' => 5,
                    'text' => 'أفضل مطعم في المنطقة! راضية تماماً عن كل شيء. سأنصح الجميع بزيارته.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.97,
                    'categories' => ['food', 'service', 'ambiance'],
                    'keywords' => ['أفضل', 'مطعم', 'راضية'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(4),
                    'owner_reply' => 'شكراً نورة! ننتظر زيارتك القادمة.',
                    'owner_reply_date' => Carbon::now()->subDays(3),
                ],
                [
                    'reviewer_name' => 'إبراهيم العمار',
                    'rating' => 5,
                    'text' => 'خمس نجوم بجدارة! الطعام ممتاز والأسعار معقولة. رضا تام.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.96,
                    'categories' => ['food', 'price'],
                    'keywords' => ['ممتاز', 'أسعار', 'معقولة', 'رضا'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(11),
                    'owner_reply' => 'شكراً إبراهيم!',
                    'owner_reply_date' => Carbon::now()->subDays(10),
                ],
                [
                    'reviewer_name' => 'هيا الناصر',
                    'rating' => 5,
                    'text' => 'رضا كامل! تجربة رائعة من البداية للنهاية. سأعود بالتأكيد.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.95,
                    'categories' => ['service', 'food', 'ambiance'],
                    'keywords' => ['رضا', 'كامل', 'رائعة'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(19),
                    'owner_reply' => null,
                    'owner_reply_date' => null,
                ],
            ],

            // Branch 10: Mixed reviews for competition variety
            10 => [
                [
                    'reviewer_name' => 'زياد الثقفي',
                    'rating' => 4,
                    'text' => 'الطعم جيد والموظف يوسف كان متعاوناً. يستحق الزيارة.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.80,
                    'categories' => ['taste', 'staff', 'service'],
                    'keywords' => ['طعم', 'جيد', 'يوسف', 'متعاون'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(3),
                    'owner_reply' => 'شكراً زياد!',
                    'owner_reply_date' => Carbon::now()->subDays(2),
                ],
                [
                    'reviewer_name' => 'أمل الشمراني',
                    'rating' => 3,
                    'text' => 'تجربة عادية. الطعم مقبول لكن الأسعار مرتفعة قليلاً.',
                    'sentiment' => 'neutral',
                    'sentiment_score' => 0.15,
                    'categories' => ['taste', 'price'],
                    'keywords' => ['عادية', 'طعم', 'مقبول', 'أسعار'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(8),
                    'owner_reply' => null,
                    'owner_reply_date' => null,
                ],
                [
                    'reviewer_name' => 'راشد المري',
                    'rating' => 4,
                    'text' => 'الطعام لذيذ والخدمة سريعة. الأخ علي كان مساعداً جداً.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.82,
                    'categories' => ['food', 'speed', 'staff'],
                    'keywords' => ['لذيذ', 'سريعة', 'علي'],
                    'reviewer_gender' => 'male',
                    'review_date' => Carbon::now()->subDays(13),
                    'owner_reply' => 'شكراً راشد! سننقل شكرك لعلي.',
                    'owner_reply_date' => Carbon::now()->subDays(12),
                ],
                [
                    'reviewer_name' => 'لطيفة العجمي',
                    'rating' => 5,
                    'text' => 'الطعم ممتاز! الموظف حسن كان رائعاً في التعامل والخدمة.',
                    'sentiment' => 'positive',
                    'sentiment_score' => 0.90,
                    'categories' => ['taste', 'staff', 'service'],
                    'keywords' => ['طعم', 'ممتاز', 'حسن', 'رائع'],
                    'reviewer_gender' => 'female',
                    'review_date' => Carbon::now()->subDays(21),
                    'owner_reply' => null,
                    'owner_reply_date' => null,
                ],
            ],

            default => [],
        };
    }

    /**
     * Get a random employee name for mentions
     */
    private function getRandomEmployee(): string
    {
        return $this->employeeNames[array_rand($this->employeeNames)];
    }

    /**
     * Calculate quality score based on text length
     */
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
