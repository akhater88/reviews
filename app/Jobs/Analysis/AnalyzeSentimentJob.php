<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class AnalyzeSentimentJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
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
