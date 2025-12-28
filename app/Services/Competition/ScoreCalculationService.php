<?php

namespace App\Services\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionReview;
use App\Models\Competition\CompetitionScore;
use App\Models\Competition\CompetitionScoreHistory;
use Illuminate\Support\Facades\Log;

class ScoreCalculationService
{
    protected array $weights;
    protected array $config;

    public function __construct()
    {
        $this->weights = config('competition.scoring.weights');
        $this->config = config('competition.scoring');
    }

    /**
     * Calculate score for a single branch in a period
     */
    public function calculateBranchScore(
        CompetitionBranch $branch,
        CompetitionPeriod $period
    ): CompetitionScore {
        $score = CompetitionScore::firstOrCreate(
            [
                'competition_period_id' => $period->id,
                'competition_branch_id' => $branch->id,
            ],
            [
                'analysis_status' => 'pending',
            ]
        );

        try {
            $score->update(['analysis_status' => 'analyzing']);

            // Get reviews for this branch
            $reviews = $this->getBranchReviews($branch, $period);

            // Calculate individual scores
            $ratingScore = $this->calculateRatingScore($branch);
            $sentimentScore = $this->calculateSentimentScore($reviews);
            $responseRate = $this->calculateResponseRate($reviews);
            $volumeScore = $this->calculateVolumeScore($reviews->count());
            $trendScore = $this->calculateTrendScore($branch, $reviews);
            $keywordScore = $this->calculateKeywordScore($reviews);

            // Calculate weighted total
            $competitionScore = $this->calculateWeightedTotal([
                'rating' => $ratingScore,
                'sentiment' => $sentimentScore,
                'response_rate' => $responseRate,
                'volume' => $volumeScore,
                'trend' => $trendScore,
                'keywords' => $keywordScore,
            ]);

            // Update score record
            $score->update([
                'overall_rating' => $branch->google_rating,
                'total_reviews' => $branch->google_reviews_count,
                'analyzed_reviews' => $reviews->whereNotNull('sentiment_score')->count(),
                'rating_score' => $ratingScore,
                'sentiment_score' => $sentimentScore,
                'response_rate' => $responseRate,
                'review_volume_score' => $volumeScore,
                'trend_score' => $trendScore,
                'keyword_score' => $keywordScore,
                'competition_score' => $competitionScore,
                'analysis_status' => 'completed',
                'last_analyzed_at' => now(),
            ]);

            // Record history
            $this->recordScoreHistory($score);

            Log::info('Competition score calculated', [
                'branch_id' => $branch->id,
                'period_id' => $period->id,
                'score' => $competitionScore,
            ]);

            return $score->fresh();

        } catch (\Exception $e) {
            $score->update([
                'analysis_status' => 'failed',
                'analysis_error' => $e->getMessage(),
            ]);

            Log::error('Competition score calculation failed', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate rating score (0-25 points)
     * Rating 5.0 = 25 points, Rating 1.0 = 5 points
     */
    protected function calculateRatingScore(CompetitionBranch $branch): float
    {
        $rating = $branch->google_rating ?? 0;
        $maxPoints = $this->weights['rating'];

        // Linear scale: (rating - 1) / 4 * maxPoints
        // Rating 5 = 25, Rating 4 = 20, Rating 3 = 15, etc.
        $score = (($rating - 1) / 4) * $maxPoints;

        return max(0, min($maxPoints, round($score, 2)));
    }

    /**
     * Calculate sentiment score (0-30 points)
     * Based on AI analysis of review text
     */
    protected function calculateSentimentScore($reviews): float
    {
        $maxPoints = $this->weights['sentiment'];

        if ($reviews->isEmpty()) {
            return 0;
        }

        $analyzedReviews = $reviews->whereNotNull('sentiment_score');

        if ($analyzedReviews->isEmpty()) {
            return 0;
        }

        // Average sentiment (0-100) -> (0-30)
        $avgSentiment = $analyzedReviews->avg('sentiment_score');
        $score = ($avgSentiment / 100) * $maxPoints;

        return round($score, 2);
    }

    /**
     * Calculate response rate score (0-15 points)
     * 100% response rate = 15 points
     */
    protected function calculateResponseRate($reviews): float
    {
        $maxPoints = $this->weights['response_rate'];

        if ($reviews->isEmpty()) {
            return 0;
        }

        $totalReviews = $reviews->count();
        $respondedReviews = $reviews->where('has_owner_response', true)->count();

        $rate = ($respondedReviews / $totalReviews) * 100;
        $score = ($rate / 100) * $maxPoints;

        return round($score, 2);
    }

    /**
     * Calculate volume score (0-10 points)
     * More reviews = higher score, capped at max
     */
    protected function calculateVolumeScore(int $reviewCount): float
    {
        $maxPoints = $this->weights['volume'];
        $maxReviews = $this->config['volume_max'];

        // Linear scale for volume
        $normalized = min($reviewCount / $maxReviews, 1);
        $score = $normalized * $maxPoints;

        return round($score, 2);
    }

    /**
     * Calculate trend score (0-10 points)
     * Improvement in recent reviews vs older reviews
     */
    protected function calculateTrendScore(CompetitionBranch $branch, $reviews): float
    {
        $maxPoints = $this->weights['trend'];
        $trendDays = $this->config['trend_period_days'];

        if ($reviews->count() < 5) {
            return $maxPoints / 2; // Neutral score for insufficient data
        }

        $cutoffDate = now()->subDays($trendDays);

        $recentReviews = $reviews->filter(fn ($r) => $r->review_date >= $cutoffDate);
        $olderReviews = $reviews->filter(fn ($r) => $r->review_date < $cutoffDate);

        if ($recentReviews->isEmpty() || $olderReviews->isEmpty()) {
            return $maxPoints / 2; // Neutral
        }

        $recentAvg = $recentReviews->avg('rating');
        $olderAvg = $olderReviews->avg('rating');

        // Calculate improvement (-1 to +1)
        $improvement = ($recentAvg - $olderAvg) / 4; // Normalize to -1 to +1

        // Convert to score (0 to max)
        $score = (($improvement + 1) / 2) * $maxPoints;

        return round($score, 2);
    }

    /**
     * Calculate keyword score (0-10 points)
     * Based on positive keywords in reviews
     */
    protected function calculateKeywordScore($reviews): float
    {
        $maxPoints = $this->weights['keywords'];

        if ($reviews->isEmpty()) {
            return 0;
        }

        $analyzedReviews = $reviews->whereNotNull('keywords');

        if ($analyzedReviews->isEmpty()) {
            return 0;
        }

        // Positive keywords list
        $positiveKeywords = [
            'ممتاز', 'رائع', 'لذيذ', 'نظيف', 'سريع', 'مميز',
            'احترافي', 'جودة', 'طازج', 'أنصح', 'أفضل', 'محترم',
            'excellent', 'amazing', 'delicious', 'clean', 'fast', 'best',
            'quality', 'fresh', 'recommend', 'professional', 'great',
        ];

        $totalKeywords = 0;
        $positiveCount = 0;

        foreach ($analyzedReviews as $review) {
            $keywords = is_array($review->keywords) ? $review->keywords : [];
            $totalKeywords += count($keywords);

            foreach ($keywords as $keyword) {
                if (in_array(strtolower($keyword), array_map('strtolower', $positiveKeywords))) {
                    $positiveCount++;
                }
            }
        }

        if ($totalKeywords === 0) {
            return $maxPoints / 2; // Neutral
        }

        $positiveRatio = $positiveCount / $totalKeywords;
        $score = $positiveRatio * $maxPoints;

        return round($score, 2);
    }

    /**
     * Calculate weighted total score
     */
    protected function calculateWeightedTotal(array $scores): float
    {
        $total = 0;

        foreach ($scores as $value) {
            $total += $value;
        }

        return round($total, 2);
    }

    /**
     * Get branch reviews for the period
     */
    protected function getBranchReviews(CompetitionBranch $branch, CompetitionPeriod $period)
    {
        return CompetitionReview::where('competition_branch_id', $branch->id)
            ->where('review_date', '>=', $period->starts_at)
            ->where('review_date', '<=', $period->ends_at)
            ->orderByDesc('review_date')
            ->get();
    }

    /**
     * Record score in history
     */
    protected function recordScoreHistory(CompetitionScore $score): void
    {
        CompetitionScoreHistory::create([
            'competition_score_id' => $score->id,
            'competition_score' => $score->competition_score,
            'rank_position' => $score->rank_position,
            'rating_score' => $score->rating_score ?? 0,
            'sentiment_score' => $score->sentiment_score,
            'response_rate' => $score->response_rate,
            'volume_score' => $score->review_volume_score,
            'trend_score' => $score->trend_score,
            'keyword_score' => $score->keyword_score,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Recalculate all scores for a period
     */
    public function recalculateAllScores(CompetitionPeriod $period): int
    {
        $branches = CompetitionBranch::whereHas('nominations', function ($q) use ($period) {
            $q->where('competition_period_id', $period->id);
        })->get();

        $calculated = 0;

        foreach ($branches as $branch) {
            try {
                $this->calculateBranchScore($branch, $period);
                $calculated++;
            } catch (\Exception $e) {
                Log::error('Failed to calculate score', [
                    'branch_id' => $branch->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $calculated;
    }
}
