<?php

namespace App\DataTransferObjects\InternalCompetition;

readonly class ResponseTimeScoreData
{
    public function __construct(
        public int $branchId,
        public int $totalReviews,
        public int $respondedReviews,
        public int $unrepliedReviews,
        public float $averageResponseTimeHours,
        public float $responseRate,
        public float $score,
        public array $responseTimeDistribution = [],
    ) {}

    /**
     * Calculate score from response metrics.
     *
     * Scoring logic:
     * - Response rate contributes 40% (percentage of reviews replied to)
     * - Response speed contributes 60% (faster = higher score)
     *
     * Response time scoring (out of 100):
     * - Under 1 hour: 100
     * - 1-4 hours: 90
     * - 4-12 hours: 75
     * - 12-24 hours: 60
     * - 24-48 hours: 40
     * - 48-72 hours: 20
     * - Over 72 hours: 10
     */
    public static function fromResponseData(
        int $branchId,
        int $totalReviews,
        int $respondedReviews,
        float $averageResponseTimeHours
    ): self {
        $unrepliedReviews = $totalReviews - $respondedReviews;
        $responseRate = $totalReviews > 0
            ? ($respondedReviews / $totalReviews) * 100
            : 0;

        // Calculate speed score based on average response time
        $speedScore = self::calculateSpeedScore($averageResponseTimeHours);

        // Combined score: Response rate (40%) + Speed score (60%)
        $score = ($responseRate * 0.4) + ($speedScore * 0.6);

        return new self(
            branchId: $branchId,
            totalReviews: $totalReviews,
            respondedReviews: $respondedReviews,
            unrepliedReviews: $unrepliedReviews,
            averageResponseTimeHours: round($averageResponseTimeHours, 2),
            responseRate: round($responseRate, 2),
            score: round($score, 2),
        );
    }

    /**
     * Calculate speed score based on average response time.
     */
    private static function calculateSpeedScore(float $hours): float
    {
        return match (true) {
            $hours < 1 => 100,
            $hours < 4 => 90,
            $hours < 12 => 75,
            $hours < 24 => 60,
            $hours < 48 => 40,
            $hours < 72 => 20,
            default => 10,
        };
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            branchId: $data['branch_id'],
            totalReviews: $data['total_reviews'] ?? 0,
            respondedReviews: $data['responded_reviews'] ?? 0,
            unrepliedReviews: $data['unreplied_reviews'] ?? 0,
            averageResponseTimeHours: $data['average_response_time_hours'] ?? 0,
            responseRate: $data['response_rate'] ?? 0,
            score: $data['score'] ?? 0,
            responseTimeDistribution: $data['response_time_distribution'] ?? [],
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'branch_id' => $this->branchId,
            'total_reviews' => $this->totalReviews,
            'responded_reviews' => $this->respondedReviews,
            'unreplied_reviews' => $this->unrepliedReviews,
            'average_response_time_hours' => $this->averageResponseTimeHours,
            'response_rate' => $this->responseRate,
            'score' => $this->score,
            'response_time_distribution' => $this->responseTimeDistribution,
        ];
    }
}
