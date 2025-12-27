<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class GenerateOperationalIntelligenceJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $sentiment = $this->getPreviousResult(AnalysisType::SENTIMENT);
            $recommendations = $this->getPreviousResult(AnalysisType::RECOMMENDATIONS);

            $result = $this->callAI($this->buildPrompt($sentiment, $recommendations));

            $this->saveAnalysis($result, AnalysisType::OPERATIONAL_INTELLIGENCE);
            $this->updateProgress(50, AnalysisStep::GENERATE_OPERATIONAL_INTELLIGENCE->value);

            // Dispatch next job
            AnalyzeCategoriesJob::dispatch(
                $this->restaurantId,
                $this->reviews,
                $this->analysisOverviewId
            );

        } catch (\Exception $e) {
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(array $sentiment, array $recommendations): string
    {
        $reviewsText = $this->formatReviewsForPrompt();
        $sentimentJson = json_encode($sentiment, JSON_UNESCAPED_UNICODE);
        $recommendationsJson = json_encode($recommendations, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
كخبير في الذكاء التشغيلي للمطاعم، حلل البيانات التالية واستخرج رؤى تشغيلية:

المراجعات:
{$reviewsText}

تحليل المشاعر:
{$sentimentJson}

التوصيات:
{$recommendationsJson}

أرجع JSON بالتنسيق التالي:
{
  "operationalCategories": [
    {
      "name": "اسم الفئة التشغيلية (جودة الخدمة، أداء الموظفين، البنية التحتية)",
      "insights": ["رؤية تشغيلية محددة مع أدلة"],
      "riskLevel": "high" أو "medium" أو "low",
      "actionRequired": true أو false,
      "evidenceCount": عدد المراجعات الداعمة,
      "specificIssues": ["مشاكل محددة"],
      "businessImpact": "التأثير على الأعمال"
    }
  ],
  "aiInsights": {
    "overallAssessment": "تقييم شامل للوضع التشغيلي",
    "priorityActions": ["إجراءات عاجلة"],
    "competitiveInsights": {
      "strongestAdvantage": "الميزة التنافسية الأقوى",
      "biggestWeakness": "نقطة الضعف الأكبر",
      "marketPosition": "موقع السوق"
    },
    "operationalEfficiency": {
      "strengths": ["نقاط القوة"],
      "bottlenecks": ["الاختناقات"],
      "recommendations": ["توصيات"]
    },
    "customerJourney": {
      "entryExperience": "تجربة الدخول",
      "serviceFlow": "تدفق الخدمة",
      "exitExperience": "تجربة المغادرة"
    }
  }
}
PROMPT;
    }
}
