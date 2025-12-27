<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;
use App\Models\Review;
use Illuminate\Support\Facades\Log;

class AnalyzeSentimentJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            // Step 1: Analyze individual review sentiments and update reviews table
            $this->analyzeIndividualReviews();

            // Step 2: Generate aggregate sentiment analysis for branch report
            $result = $this->callAI($this->buildPrompt(), [
                'system_message' => 'أنت ذكاء اصطناعي خبير في تحليل المشاعر متخصص في مراجعات المطاعم. حلل المشاعر حسب الفئات المكتشفة من المراجعات الفعلية. يجب أن تكون جميع العبارات المفتاحية والتحليلات باللغة العربية. اجب دائماً بـ JSON صالح فقط، بدون نص إضافي.',
            ]);

            $this->saveAnalysis($result, AnalysisType::SENTIMENT);
            $this->updateProgress(20, AnalysisStep::ANALYZE_SENTIMENT->value);

            // Dispatch next job
            GenerateRecommendationsJob::dispatch(
                $this->restaurantId,
                $this->reviews,
                $this->analysisOverviewId
            );

        } catch (\Exception $e) {
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze and update sentiment for each individual review
     */
    private function analyzeIndividualReviews(): void
    {
        // Filter reviews that have text and need sentiment analysis
        $reviewsToAnalyze = collect($this->reviews)
            ->filter(fn($r) => !empty($r['text']) && !empty($r['id']));

        if ($reviewsToAnalyze->isEmpty()) {
            Log::info("No reviews with text to analyze for sentiment", [
                'restaurant_id' => $this->restaurantId,
            ]);

            // Still update star-only reviews with rating-based sentiment
            $this->updateStarOnlyReviews();
            return;
        }

        // Process in batches to avoid token limits
        $batches = $reviewsToAnalyze->chunk(20);

        foreach ($batches as $batch) {
            $this->processBatch($batch->toArray());
        }

        // Also update star-only reviews using rating-based sentiment
        $this->updateStarOnlyReviews();

        Log::info("Individual review sentiments updated", [
            'restaurant_id' => $this->restaurantId,
            'reviews_analyzed' => $reviewsToAnalyze->count(),
        ]);
    }

    /**
     * Process a batch of reviews for sentiment analysis
     */
    private function processBatch(array $reviews): void
    {
        $prompt = $this->buildBatchSentimentPrompt($reviews);

        try {
            $result = $this->callAI($prompt, [
                'system_message' => 'أنت محلل مشاعر. حلل كل مراجعة وحدد إذا كانت إيجابية أو محايدة أو سلبية. أجب بـ JSON فقط.',
                'max_tokens' => 2000,
            ]);

            $sentiments = $result['result']['reviews'] ?? [];

            // Update each review in database
            foreach ($sentiments as $item) {
                $reviewId = $item['id'] ?? null;
                $sentiment = $item['sentiment'] ?? null;

                if ($reviewId && $sentiment && in_array($sentiment, ['positive', 'neutral', 'negative'])) {
                    Review::where('id', $reviewId)->update([
                        'sentiment' => $sentiment,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::warning("Batch sentiment analysis failed, using fallback", [
                'error' => $e->getMessage(),
            ]);

            // Fallback: Use rating-based sentiment
            $this->fallbackSentimentByRating($reviews);
        }
    }

    /**
     * Build prompt for batch sentiment analysis
     */
    private function buildBatchSentimentPrompt(array $reviews): string
    {
        $reviewsJson = collect($reviews)->map(function ($r) {
            return [
                'id' => $r['id'],
                'rating' => $r['rating'] ?? null,
                'text' => mb_substr($r['text'] ?? '', 0, 500), // Limit text length
            ];
        })->toJson(JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
حلل المشاعر لكل مراجعة من المراجعات التالية.

المراجعات:
{$reviewsJson}

أرجع JSON بالتنسيق التالي:
{
  "reviews": [
    {"id": 1, "sentiment": "positive"},
    {"id": 2, "sentiment": "negative"},
    {"id": 3, "sentiment": "neutral"}
  ]
}

قواعد التصنيف:
- "positive": مراجعة إيجابية تعبر عن رضا العميل
- "negative": مراجعة سلبية تعبر عن عدم رضا أو شكوى
- "neutral": مراجعة محايدة أو مختلطة

ملاحظات:
- يجب أن يتطابق id مع id المراجعة المقدمة
- أجب بـ JSON فقط بدون أي نص إضافي
PROMPT;
    }

    /**
     * Fallback: Determine sentiment based on rating if AI fails
     */
    private function fallbackSentimentByRating(array $reviews): void
    {
        foreach ($reviews as $review) {
            $reviewId = $review['id'] ?? null;
            $rating = $review['rating'] ?? 3;

            if (!$reviewId) continue;

            $sentiment = match (true) {
                $rating >= 4 => 'positive',
                $rating <= 2 => 'negative',
                default => 'neutral',
            };

            Review::where('id', $reviewId)->update([
                'sentiment' => $sentiment,
            ]);
        }

        Log::info("Fallback sentiment by rating applied", [
            'restaurant_id' => $this->restaurantId,
            'reviews_count' => count($reviews),
        ]);
    }

    /**
     * Update star-only reviews (no text) with rating-based sentiment
     */
    private function updateStarOnlyReviews(): void
    {
        $starOnlyReviews = collect($this->reviews)
            ->filter(fn($r) => empty($r['text']) && !empty($r['id']));

        if ($starOnlyReviews->isEmpty()) {
            return;
        }

        foreach ($starOnlyReviews as $review) {
            $reviewId = $review['id'] ?? null;
            $rating = $review['rating'] ?? 3;

            if (!$reviewId) continue;

            $sentiment = match (true) {
                $rating >= 4 => 'positive',
                $rating <= 2 => 'negative',
                default => 'neutral',
            };

            Review::where('id', $reviewId)->update([
                'sentiment' => $sentiment,
            ]);
        }

        Log::info("Star-only reviews sentiment updated by rating", [
            'restaurant_id' => $this->restaurantId,
            'count' => $starOnlyReviews->count(),
        ]);
    }

    private function buildPrompt(): string
    {
        $totalReviews = count($this->reviews);
        $avgRating = collect($this->reviews)->avg('rating') ?: 0;
        $reviewsText = $this->formatReviewsForPrompt();

        return <<<PROMPT
بناءً على البيانات المستخرجة التالية، اكتب تحليل مشاعر شامل:

البيانات الإحصائية:
- إجمالي المراجعات: {$totalReviews}
- متوسط التقييم: {$avgRating}/5

المراجعات:
{$reviewsText}

أرجع JSON بالتنسيق التالي:
{
  "overallSentiment": "positive" أو "neutral" أو "negative",
  "sentimentDistribution": {
    "positive": النسبة المئوية الدقيقة للمشاعر الإيجابية (مثال: 37.2),
    "neutral": النسبة المئوية الدقيقة للمشاعر المحايدة (مثال: 24.8),
    "negative": النسبة المئوية الدقيقة للمشاعر السلبية (مثال: 38.0)
  },
  "keyInsights": [
    "الرؤى الأساسية من تحليل المشاعر - 3-5 رؤى"
  ],
  "customerQuotes": {
    "positive": ["العبارات الإيجابية من المراجعات بدون تكرار - 3-5 عبارات"],
    "negative": ["العبارات السلبية من المراجعات بدون تكرار - 3-5 عبارات"],
    "neutral": ["العبارات المحايدة من المراجعات بدون تكرار - 2-3 عبارات"]
  }
}

ملاحظات مهمة:
1. استخدم فقط النصوص الموجودة في المراجعات المقدمة أعلاه
2. إذا لم توجد عبارات كافية، اترك المصفوفة فارغة []
3. كل اقتباس يجب أن يكون نسخة دقيقة من عبارة العميل - بدون تعديل أو إضافة
4. اجب دائماً بـ JSON صالح فقط، بدون نص إضافي
PROMPT;
    }
}
