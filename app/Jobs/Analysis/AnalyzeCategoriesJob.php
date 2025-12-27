<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class AnalyzeCategoriesJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $result = $this->callAI($this->buildPrompt());

            $this->saveAnalysis($result, AnalysisType::CATEGORY_INSIGHTS);
            $this->updateProgress(60, AnalysisStep::ANALYZE_CATEGORIES->value);

            // Dispatch next job
            AnalyzeEmployeesJob::dispatch(
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
        $reviewsText = $this->formatReviewsForPrompt();
        $totalReviews = count($this->reviews);

        return <<<PROMPT
أنت محلل خبير للمراجعات العربية للمطاعم. حلل المراجعات التالية واستخرج الفئات الرئيسية.

الفئات المحتملة:
- الطعام/الطعم
- السعر/التسعير
- الخدمة/المعاملة
- النظافة
- البيئة/المكان/الأجواء
- السرعة/الانتظار

المراجعات:
{$reviewsText}

أرجع JSON بالتنسيق التالي:
{
  "categories": [
    {
      "name": "اسم الفئة",
      "rating": 4.2,
      "positiveCount": عدد التعليقات الإيجابية,
      "negativeCount": عدد التعليقات السلبية,
      "mixedCount": عدد التعليقات المختلطة,
      "totalMentions": إجمالي الذكر,
      "positiveExamples": ["اقتباس إيجابي حقيقي"],
      "negativeExamples": ["اقتباس سلبي حقيقي"],
      "overallSentiment": "positive" أو "negative" أو "neutral",
      "confidenceScore": 0.85
    }
  ],
  "bestCategory": {
    "name": "اسم أفضل فئة",
    "rating": التقييم,
    "reason": "سبب كونها الأفضل",
    "evidenceQuote": "اقتباس داعم"
  },
  "worstCategory": {
    "name": "اسم أسوأ فئة",
    "rating": التقييم,
    "reason": "سبب كونها الأسوأ",
    "evidenceQuote": "اقتباس داعم"
  },
  "analysisMetadata": {
    "totalReviews": {$totalReviews},
    "confidence": 0.9
  }
}

تعليمات:
- استخدم فقط الاقتباسات الحقيقية من المراجعات
- احسب الإحصاءات بدقة
- التقييم يعكس نسبة الإيجابي/السلبي الفعلية
PROMPT;
    }
}
