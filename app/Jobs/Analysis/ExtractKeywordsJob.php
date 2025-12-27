<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class ExtractKeywordsJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $result = $this->callAI($this->buildPrompt());

            $this->saveAnalysis($result, AnalysisType::KEYWORDS);
            $this->updateProgress(40, AnalysisStep::EXTRACT_KEYWORDS->value);

            // Dispatch next job
            GenerateOperationalIntelligenceJob::dispatch(
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
        $reviewsWithText = $this->getReviewsWithText();
        $starOnlyReviews = $this->getStarOnlyReviews();

        $reviewsWithTextCount = count($reviewsWithText);
        $starOnlyCount = count($starOnlyReviews);

        $reviewsFormatted = collect($reviewsWithText)->map(function ($review, $idx) {
            $date = $review['review_date'] ?? 'غير محدد';
            $rating = $review['rating'] ?? 5;
            $author = $review['author_name'] ?? 'عميل';
            $text = $review['text'];
            return ($idx + 1) . ". التاريخ: {$date} | التقييم: {$rating}/5 | المؤلف: {$author}\nالنص: \"{$text}\"";
        })->implode("\n\n");

        return <<<PROMPT
أنت محلل ذكي متخصص في استخراج البيانات المنظمة من مراجعات المطاعم.

معطيات التحليل:
- المراجعات النصية: {$reviewsWithTextCount}
- المراجعات النجمية فقط: {$starOnlyCount}

المراجعات النصية:
{$reviewsFormatted}

استخرج البيانات التالية بصيغة JSON:

{
  "aspects": [
    {
      "name": "اسم الجانب (مثل: الطعام، الخدمة، السعر، النظافة)",
      "positiveCount": عدد المراجعات الإيجابية,
      "negativeCount": عدد المراجعات السلبية,
      "neutralCount": عدد المراجعات المحايدة,
      "topPhrasesPositive": ["عبارات إيجابية من نصوص العملاء"],
      "topPhrasesNegative": ["عبارات سلبية من نصوص العملاء"],
      "confidence": نسبة الثقة من 0 إلى 1
    }
  ],
  "keywordGroups": [
    {
      "mainKeyword": "الكلمة أو العبارة الأكثر تكراراً",
      "frequency": عدد مرات الذكر,
      "sentiment": "positive" أو "negative" أو "neutral",
      "synonyms": ["مرادفات وعبارات مشابهة"]
    }
  ],
  "foodItems": [
    {
      "name": "اسم الطبق أو المشروب",
      "mentions": عدد مرات الذكر,
      "sentiment": "positive" أو "negative" أو "mixed",
      "averageRating": متوسط التقييم
    }
  ],
  "customerQuotes": {
    "positive": ["اقتباسات إيجابية كاملة"],
    "negative": ["اقتباسات سلبية كاملة"],
    "neutral": ["اقتباسات محايدة كاملة"]
  }
}

قواعد مهمة:
1. keywordGroups = عبارات عن الخدمة والجودة (بدون أسماء أطعمة)
2. foodItems = أسماء الأطعمة والمشروبات فقط
3. استخدم عبارات العملاء الأصلية بالضبط
4. تأكد من وجود 10-15 keywordGroups و 15-20 foodItems
PROMPT;
    }
}
