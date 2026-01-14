<?php

namespace App\Jobs;

use App\Models\FreeReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeFreeReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600; // 10 minutes for AI analysis
    public int $backoff = 120; // 2 minutes between retries

    public function __construct(
        public FreeReport $report
    ) {}

    public function handle(): void
    {
        Log::info('AnalyzeFreeReportJob: Starting', [
            'report_id' => $this->report->id,
        ]);

        try {
            $reviews = $this->report->reviews()->get();

            if ($reviews->isEmpty()) {
                Log::info('AnalyzeFreeReportJob: No reviews to analyze', [
                    'report_id' => $this->report->id,
                ]);

                // Generate basic results without AI analysis
                $analysisData = $this->generateBasicAnalysis();
            } else {
                // Perform AI analysis
                $analysisData = $this->performAIAnalysis($reviews->toArray());
            }

            Log::info('AnalyzeFreeReportJob: Analysis completed', [
                'report_id' => $this->report->id,
            ]);

            // Dispatch next job in pipeline
            GenerateFreeReportResultsJob::dispatch($this->report, $analysisData);

        } catch (\Exception $e) {
            Log::error('AnalyzeFreeReportJob: Failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Perform AI analysis on reviews.
     */
    protected function performAIAnalysis(array $reviews): array
    {
        $apiKey = config('ai.openai_api_key') ?? config('ai.anthropic_api_key');

        // If no AI key configured, use basic analysis
        if (!$apiKey) {
            Log::info('AnalyzeFreeReportJob: No AI key configured, using basic analysis');
            return $this->generateBasicAnalysis($reviews);
        }

        // Prepare reviews text for analysis
        $reviewsText = collect($reviews)
            ->take(50) // Limit to 50 reviews for AI context
            ->map(fn($r) => "تقييم {$r['rating']}/5: {$r['text']}")
            ->filter(fn($r) => !empty($r))
            ->join("\n\n");

        $prompt = $this->buildAnalysisPrompt($reviewsText);

        try {
            // Try OpenAI first
            if (config('ai.openai_api_key')) {
                return $this->analyzeWithOpenAI($prompt);
            }

            // Fallback to Anthropic
            if (config('ai.anthropic_api_key')) {
                return $this->analyzeWithAnthropic($prompt);
            }

        } catch (\Exception $e) {
            Log::warning('AnalyzeFreeReportJob: AI analysis failed, using basic', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->generateBasicAnalysis($reviews);
    }

    /**
     * Analyze with OpenAI.
     */
    protected function analyzeWithOpenAI(string $prompt): array
    {
        $response = Http::withToken(config('ai.openai_api_key'))
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('ai.openai_model', 'gpt-4-turbo-preview'),
                'messages' => [
                    ['role' => 'system', 'content' => 'أنت محلل تقييمات خبير. قم بتحليل التقييمات وإرجاع النتائج بتنسيق JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        return json_decode($content, true) ?? $this->generateBasicAnalysis();
    }

    /**
     * Analyze with Anthropic Claude.
     */
    protected function analyzeWithAnthropic(string $prompt): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('ai.anthropic_api_key'),
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(120)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('ai.anthropic_model', 'claude-3-sonnet-20240229'),
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Anthropic API error: ' . $response->body());
        }

        $content = $response->json('content.0.text');

        // Extract JSON from response
        preg_match('/\{[\s\S]*\}/', $content, $matches);
        return json_decode($matches[0] ?? '{}', true) ?? $this->generateBasicAnalysis();
    }

    /**
     * Build analysis prompt.
     */
    protected function buildAnalysisPrompt(string $reviewsText): string
    {
        return <<<PROMPT
حلل التقييمات التالية للمطعم "{$this->report->business_name}" وأرجع النتائج بتنسيق JSON:

التقييمات:
{$reviewsText}

أرجع JSON بالهيكل التالي:
{
    "overall_score": (رقم من 1-10),
    "sentiment_breakdown": {
        "positive": (عدد التقييمات الإيجابية),
        "neutral": (عدد التقييمات المحايدة),
        "negative": (عدد التقييمات السلبية)
    },
    "category_scores": {
        "food_quality": (رقم من 1-10),
        "service": (رقم من 1-10),
        "cleanliness": (رقم من 1-10),
        "value_for_money": (رقم من 1-10),
        "ambiance": (رقم من 1-10)
    },
    "top_strengths": ["نقطة قوة 1", "نقطة قوة 2", "نقطة قوة 3"],
    "top_weaknesses": ["نقطة ضعف 1", "نقطة ضعف 2", "نقطة ضعف 3"],
    "keyword_analysis": {
        "positive_keywords": ["كلمة 1", "كلمة 2"],
        "negative_keywords": ["كلمة 1", "كلمة 2"]
    },
    "executive_summary": "ملخص تنفيذي في 2-3 جمل",
    "recommendations": ["توصية 1", "توصية 2", "توصية 3"]
}
PROMPT;
    }

    /**
     * Generate basic analysis without AI.
     */
    protected function generateBasicAnalysis(?array $reviews = null): array
    {
        $reviews = $reviews ?? $this->report->reviews()->get()->toArray();

        $totalReviews = count($reviews);
        $totalRating = array_sum(array_column($reviews, 'rating'));
        $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;

        $positive = count(array_filter($reviews, fn($r) => ($r['rating'] ?? 0) >= 4));
        $negative = count(array_filter($reviews, fn($r) => ($r['rating'] ?? 0) <= 2));
        $neutral = $totalReviews - $positive - $negative;

        $overallScore = $averageRating * 2; // Convert 5-star to 10-point scale

        return [
            'overall_score' => $overallScore,
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating,
            'sentiment_breakdown' => [
                'positive' => $positive,
                'neutral' => $neutral,
                'negative' => $negative,
            ],
            'category_scores' => null, // Basic analysis doesn't provide category breakdown
            'top_strengths' => $positive > $negative
                ? ['تقييمات إيجابية من العملاء']
                : [],
            'top_weaknesses' => $negative > $positive
                ? ['تحتاج إلى تحسين بناءً على ملاحظات العملاء']
                : [],
            'keyword_analysis' => null,
            'executive_summary' => $this->generateBasicSummary($totalReviews, $averageRating, $positive, $negative),
            'recommendations' => $this->generateBasicRecommendations($averageRating, $positive, $negative),
        ];
    }

    /**
     * Generate basic summary.
     */
    protected function generateBasicSummary(int $total, float $avg, int $positive, int $negative): string
    {
        if ($total === 0) {
            return 'لا توجد تقييمات كافية للتحليل.';
        }

        $rating = $avg >= 4 ? 'ممتاز' : ($avg >= 3 ? 'جيد' : 'يحتاج تحسين');

        return "بناءً على {$total} تقييم، حصل المطعم على تقييم {$rating} ({$avg}/5). " .
               "{$positive} تقييم إيجابي و {$negative} تقييم سلبي.";
    }

    /**
     * Generate basic recommendations.
     */
    protected function generateBasicRecommendations(float $avg, int $positive, int $negative): array
    {
        $recommendations = [];

        if ($avg < 3) {
            $recommendations[] = 'التركيز على تحسين جودة الخدمة والمنتجات';
            $recommendations[] = 'الرد على التقييمات السلبية ومعالجة الشكاوى';
        }

        if ($negative > $positive) {
            $recommendations[] = 'تحليل أسباب التقييمات السلبية ووضع خطة تحسين';
        }

        if ($avg >= 4) {
            $recommendations[] = 'الحفاظ على مستوى الخدمة الممتاز';
            $recommendations[] = 'تشجيع العملاء الراضين على ترك تقييمات';
        }

        return $recommendations ?: ['متابعة تقييمات العملاء بشكل دوري'];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeFreeReportJob: Permanently failed', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);

        $this->report->updateStatus(
            FreeReport::STATUS_FAILED,
            'فشل في تحليل التقييمات: ' . $exception->getMessage()
        );
    }
}
