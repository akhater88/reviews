<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;

class AnalyzeEmployeesJob extends BaseAnalysisJob
{
    public function handle(): void
    {
        try {
            $result = $this->callAI($this->buildPrompt());

            $this->saveAnalysis($result, AnalysisType::EMPLOYEES_INSIGHTS);
            $this->updateProgress(70, AnalysisStep::ANALYZE_EMPLOYEES->value);

            // Dispatch next job
            GenderInsightsJob::dispatch(
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

        return <<<PROMPT
أنت محلل خبير للمراجعات. استخرج الموظفين المذكورين في المراجعات التالية:

المراجعات:
{$reviewsText}

أرجع JSON بالتنسيق التالي:
{
  "overview": {
    "mostPositiveEmployee": {
      "name": "اسم الموظف",
      "totalMentions": عدد مرات الذكر,
      "averageRating": متوسط التقييم,
      "topPositives": ["اقتباسات إيجابية مختصرة"],
      "topNegatives": ["اقتباسات سلبية"],
      "improvementPoints": ["نقاط تحسين"]
    },
    "mostMentionedEmployee": {
      "name": "اسم مختلف",
      "totalMentions": عدد,
      "averageRating": متوسط,
      "topPositives": [],
      "topNegatives": [],
      "improvementPoints": []
    },
    "mostNegativeEmployee": {
      "name": "اسم مختلف",
      "totalMentions": عدد,
      "averageRating": متوسط,
      "topPositives": [],
      "topNegatives": [],
      "improvementPoints": []
    }
  },
  "performance": [
    {
      "name": "اسم الموظف",
      "totalMentions": عدد,
      "averageRating": متوسط,
      "performanceNote": "up" أو "stable" أو "down"
    }
  ]
}

ملاحظات:
- الثلاثة في overview يجب أن يكونوا موظفين مختلفين
- إذا لم يذكر أي موظف، أرجع overview: {} و performance: []
- رتب performance حسب التقييم
PROMPT;
    }
}
