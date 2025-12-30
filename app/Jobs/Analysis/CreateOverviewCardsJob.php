<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStatus;
use App\Enums\AnalysisStep;
use App\Enums\AnalysisType;
use App\Models\AnalysisOverview;
use App\Models\AnalysisResult;
use App\Models\Branch;
use App\Services\PerformanceScoreService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateOverviewCardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;
    public int $backoff = 15;

    public function __construct(
        protected string $restaurantId,
        protected int $analysisOverviewId
    ) {
        // Use Redis connection for Horizon
        $this->onConnection(config('ai.analysis.connection', 'redis'));
        $this->onQueue(config('ai.analysis.queue', 'analysis'));
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'analysis',
            'restaurant:' . $this->restaurantId,
            'overview:' . $this->analysisOverviewId,
            'CreateOverviewCardsJob',
            'final-step',
        ];
    }

    public function handle(): void
    {
        try {
            $overview = AnalysisOverview::with('results')->findOrFail($this->analysisOverviewId);

            // Gather all results
            $sentiment = $this->getResult(AnalysisType::SENTIMENT);
            $categories = $this->getResult(AnalysisType::CATEGORY_INSIGHTS);

            // Build overview cards
            $overviewCards = [
                $this->buildRatingsCard($overview),
                $this->buildSentimentCard($sentiment),
                $this->buildCategoryCard($categories),
            ];

            // Save overview cards
            AnalysisResult::updateOrCreate([
                'analysis_overview_id' => $this->analysisOverviewId,
                'analysis_type' => AnalysisType::OVERVIEW_CARDS->value,
            ], [
                'restaurant_id' => $this->restaurantId,
                'result' => $overviewCards,
                'status' => AnalysisStatus::COMPLETED,
                'provider' => 'system',
                'model' => 'aggregation',
                'processing_time' => 0,
                'tokens_used' => 0,
                'confidence' => 0.95,
                'review_count' => $overview->total_reviews,
                'period_start' => $overview->period_start,
                'period_end' => $overview->period_end,
            ]);

            // Mark analysis as completed
            $overview->markAsCompleted();

            // Update branch performance score and status
            $this->updateBranchPerformance($overview);

            Log::info("Analysis Pipeline Completed", [
                'restaurant_id' => $this->restaurantId,
                'analysis_overview_id' => $this->analysisOverviewId,
                'total_time' => $overview->total_processing_time,
                'total_tokens' => $overview->total_tokens_used,
            ]);

        } catch (\Exception $e) {
            Log::error("CreateOverviewCardsJob failed", [
                'restaurant_id' => $this->restaurantId,
                'error' => $e->getMessage(),
            ]);

            AnalysisOverview::where('id', $this->analysisOverviewId)
                ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CreateOverviewCardsJob Failed Permanently", [
            'restaurant_id' => $this->restaurantId,
            'error' => $exception->getMessage(),
        ]);

        AnalysisOverview::where('id', $this->analysisOverviewId)
            ->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
    }

    private function getResult(AnalysisType $type): array
    {
        $result = AnalysisResult::where('analysis_overview_id', $this->analysisOverviewId)
            ->where('analysis_type', $type->value)
            ->first();

        return $result?->result ?? [];
    }

    private function buildRatingsCard(AnalysisOverview $overview): array
    {
        // Build timeline data from reviews
        $timeline = $this->buildTimelineData($overview);

        return [
            'id' => "ratings_{$this->restaurantId}",
            'title' => 'التقييمات والمراجعات',
            'type' => 'ratings_reviews',
            'data' => [
                'totalReviews' => $overview->total_reviews,
                'reviewsWithText' => $overview->reviews_with_text,
                'starOnlyReviews' => $overview->star_only_reviews,
                'periodStart' => $overview->period_start?->format('Y-m-d'),
                'periodEnd' => $overview->period_end?->format('Y-m-d'),
                'timeline' => $timeline,
            ],
        ];
    }

    /**
     * Build timeline data showing ratings trend over the analysis period
     */
    private function buildTimelineData(AnalysisOverview $overview): array
    {
        $branch = Branch::find($overview->branch_id);
        if (!$branch) {
            return [];
        }

        // Get reviews for the last 3 months
        $periodEnd = $overview->period_end ?? now();
        $periodStart = $overview->period_start ?? now()->subMonths(3);

        $reviews = $branch->reviews()
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('rating')
            ->orderBy('review_date')
            ->get(['rating', 'review_date']);

        if ($reviews->isEmpty()) {
            return [];
        }

        // Group reviews by month
        $groupedByMonth = $reviews->groupBy(function ($review) {
            return Carbon::parse($review->review_date)->format('Y-m');
        });

        $periods = [];
        $previousRating = null;

        foreach ($groupedByMonth as $month => $monthReviews) {
            $averageRating = round($monthReviews->avg('rating'), 2);
            $reviewCount = $monthReviews->count();

            // Determine trend direction
            $trendDirection = 'stable';
            if ($previousRating !== null) {
                $change = $averageRating - $previousRating;
                if ($change > 0.1) {
                    $trendDirection = 'improving';
                } elseif ($change < -0.1) {
                    $trendDirection = 'declining';
                }
            }

            $periods[] = [
                'period' => $month,
                'label' => $month,
                'averageRating' => $averageRating,
                'reviewCount' => $reviewCount,
                'trendDirection' => $trendDirection,
            ];

            $previousRating = $averageRating;
        }

        // Generate AI insights based on trend
        $aiInsights = $this->generateTimelineInsights($periods);

        return [
            'periods' => $periods,
            'aiInsights' => $aiInsights,
        ];
    }

    /**
     * Generate AI insights for timeline trend
     */
    private function generateTimelineInsights(array $periods): array
    {
        if (count($periods) < 2) {
            return [
                'overallTrend' => 'لا توجد بيانات كافية لتحليل الاتجاه الزمني',
            ];
        }

        $firstRating = $periods[0]['averageRating'];
        $lastRating = end($periods)['averageRating'];
        $change = $lastRating - $firstRating;

        if ($change > 0.2) {
            $description = 'الاتجاه الزمني يظهر تحسناً ملحوظاً في التقييمات خلال الأشهر الثلاثة الماضية';
        } elseif ($change < -0.2) {
            $description = 'الاتجاه الزمني يظهر انخفاضاً في التقييمات يتطلب الانتباه';
        } else {
            // More detailed analysis for stable trends
            if (count($periods) >= 3) {
                $middleRating = $periods[1]['averageRating'];
                $firstToMiddle = $middleRating - $firstRating;
                $middleToLast = $lastRating - $middleRating;

                if ($firstToMiddle < -0.1 && $middleToLast > 0.1) {
                    $description = 'الاتجاه الزمني يظهر تذبذب في التقييمات، مع تراجع ثم تحسن طفيف';
                } elseif ($firstToMiddle > 0.1 && $middleToLast < -0.1) {
                    $description = 'الاتجاه الزمني يظهر تحسناً في البداية ثم تراجعاً في الفترة الأخيرة';
                } else {
                    $description = 'الاتجاه الزمني يظهر استقراراً في التقييمات مع تذبذبات طفيفة';
                }
            } else {
                $description = 'الاتجاه الزمني يظهر استقراراً في التقييمات';
            }
        }

        return [
            'overallTrend' => $description,
            'change' => round($change, 2),
            'direction' => $change > 0.15 ? 'improving' : ($change < -0.15 ? 'declining' : 'stable'),
        ];
    }

    private function buildSentimentCard(array $sentiment): array
    {
        return [
            'id' => "sentiment_{$this->restaurantId}",
            'title' => 'المشاعر العامّة',
            'type' => 'general_sentiment',
            'data' => [
                'overallSentiment' => $sentiment['overallSentiment'] ?? 'neutral',
                'sentimentDistribution' => $sentiment['sentimentDistribution'] ?? [],
                'keyInsights' => $sentiment['keyInsights'] ?? [],
                'customerQuotes' => $sentiment['customerQuotes'] ?? [],
            ],
        ];
    }

    private function buildCategoryCard(array $categories): array
    {
        return [
            'id' => "categories_{$this->restaurantId}",
            'title' => 'تحليل الفئات',
            'type' => 'category_analysis',
            'data' => [
                'categories' => $categories['categories'] ?? [],
                'bestCategory' => $categories['bestCategory'] ?? null,
                'worstCategory' => $categories['worstCategory'] ?? null,
            ],
        ];
    }

    /**
     * Update branch performance score and status after analysis
     */
    private function updateBranchPerformance(AnalysisOverview $overview): void
    {
        $branch = Branch::find($overview->branch_id);
        if (!$branch) {
            return;
        }

        $performanceService = app(PerformanceScoreService::class);

        // Ensure dates are Carbon instances
        $periodStart = $overview->period_start instanceof Carbon
            ? $overview->period_start
            : Carbon::parse($overview->period_start ?? now()->startOfMonth());
        $periodEnd = $overview->period_end instanceof Carbon
            ? $overview->period_end
            : Carbon::parse($overview->period_end ?? now()->endOfMonth());

        $scoreData = $performanceService->calculateBranchScore(
            $branch,
            $periodStart,
            $periodEnd
        );

        $performanceScore = (int) round($scoreData['performance_score']);

        // Determine status based on performance score
        $status = match (true) {
            $performanceScore >= 85 => 'excellent',
            $performanceScore >= 70 => 'good',
            $performanceScore >= 50 => 'average',
            default => 'needs_improvement',
        };

        $branch->update([
            'performance_score' => $performanceScore,
            'status' => $status,
        ]);

        Log::info("Branch performance updated", [
            'branch_id' => $branch->id,
            'performance_score' => $performanceScore,
            'status' => $status,
        ]);
    }
}
