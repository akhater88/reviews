<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class GenerateRecommendationsJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $sentiment = $this->getPreviousResult(AnalysisType::SENTIMENT);

            $result = $this->callAI($this->buildPrompt($sentiment));

            $this->saveAnalysis($result, AnalysisType::RECOMMENDATIONS);
            $this->updateProgress(30, AnalysisStep::GENERATE_RECOMMENDATIONS->value);

            // Dispatch next job
            ExtractKeywordsJob::dispatch(
                $this->restaurantId,
                $this->reviews,
                $this->analysisOverviewId
            );

        } catch (\Exception $e) {
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(array $sentiment): string
    {
        $keyInsights = implode("\n- ", $sentiment['keyInsights'] ?? []);
        $negativeQuotes = implode("\n- ", array_slice($sentiment['customerQuotes']['negative'] ?? [], 0, 5));

        $distribution = $sentiment['sentimentDistribution'] ?? [];
        $positivePercent = $distribution['positive'] ?? 0;
        $neutralPercent = $distribution['neutral'] ?? 0;
        $negativePercent = $distribution['negative'] ?? 0;

        return <<<PROMPT
بناءً على تحليل المراجعات، اكتب توصيات عملية للمطعم:

الرؤى الأساسية:
- {$keyInsights}

توزيع المشاعر:
- إيجابي: {$positivePercent}%
- محايد: {$neutralPercent}%
- سلبي: {$negativePercent}%

الشكاوى الرئيسية من العملاء:
- {$negativeQuotes}

أرجع JSON بالتنسيق التالي:
{
  "immediateActions": [
    {
      "title": "العنوان",
      "description": "الوصف التفصيلي",
      "priority": "high" أو "medium" أو "low",
      "timeframe": "فوري" أو "خلال أسبوع" أو "خلال شهر",
      "expectedImpact": "التأثير المتوقع"
    }
  ],
  "strategicInitiatives": [
    {
      "title": "المبادرة الاستراتيجية",
      "description": "الوصف",
      "steps": ["الخطوات المطلوبة"],
      "timeframe": "الإطار الزمني"
    }
  ],
  "operationalImprovements": {
    "service": ["تحسينات الخدمة"],
    "food": ["تحسينات الطعام"],
    "environment": ["تحسينات البيئة"]
  }
}
PROMPT;
    }
}
