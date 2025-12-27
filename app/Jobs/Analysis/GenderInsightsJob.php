<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class GenderInsightsJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $result = $this->callAI($this->buildPrompt());

            $this->saveAnalysis($result, AnalysisType::GENDER_INSIGHTS);
            $this->updateProgress(80, AnalysisStep::GENDER_INSIGHTS->value);

            // Dispatch final job
            CreateOverviewCardsJob::dispatch(
                $this->restaurantId,
                $this->analysisOverviewId
            );

        } catch (\Exception $e) {
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(): string
    {
        $reviews = collect($this->reviews)->map(function ($review, $index) {
            $text = $review['text'] ?? '';
            $rating = $review['rating'] ?? 5;
            $author = $review['author_name'] ?? 'عميل';
            return "مراجعة " . ($index + 1) . ": {$text} (تقييم: {$rating}/5) - المؤلف: {$author}";
        })->implode("\n\n");

        return <<<PROMPT
أنت محلل بيانات متخصص. قسّم المراجعات التالية حسب الجنس (ذكور، إناث، غير مصنف):

المراجعات:
{$reviews}

أرجع JSON بالتنسيق التالي:
{
  "categories": [
    {
      "category": "ذكور",
      "totalReviews": عدد المراجعات,
      "percentage": النسبة المئوية (مثال: 35.08),
      "averageRating": متوسط التقييمات,
      "positiveCount": عدد الإيجابية,
      "negativeCount": عدد السلبية,
      "topPositives": ["اقتباسات إيجابية حقيقية"],
      "topNegatives": ["اقتباسات سلبية حقيقية"]
    },
    {
      "category": "إناث",
      ...
    },
    {
      "category": "غير مصنف",
      ...
    }
  ],
  "summary": {
    "totalAnalyzed": إجمالي المراجعات,
    "dominantGender": "الفئة الأكثر",
    "highestRatedGender": "الفئة الأعلى تقييماً"
  }
}

ملاحظات:
- مجموع النسب = 100%
- استخدم اقتباسات حقيقية فقط
- إذا لم يوجد تصنيف، ضع في "غير مصنف"
PROMPT;
    }
}
