<?php

namespace App\DataTransferObjects\InternalCompetition;

readonly class SatisfactionScoreData
{
    public function __construct(
        public int $branchId,
        public int $totalReviews,
        public int $positiveReviews,
        public int $negativeReviews,
        public int $neutralReviews,
        public float $averageRating,
        public float $satisfactionRate,
        public float $score,
        public array $ratingDistribution = [],
    ) {}

    /**
     * Create from raw review data.
     */
    public static function fromReviewData(
        int $branchId,
        int $totalReviews,
        int $positiveReviews,
        int $negativeReviews,
        int $neutralReviews,
        float $averageRating
    ): self {
        $satisfactionRate = $totalReviews > 0
            ? ($positiveReviews / $totalReviews) * 100
            : 0;

        // Score is calculated as weighted combination of satisfaction rate and average rating
        // Satisfaction rate (70%) + Normalized rating (30%)
        $normalizedRating = ($averageRating / 5) * 100;
        $score = ($satisfactionRate * 0.7) + ($normalizedRating * 0.3);

        return new self(
            branchId: $branchId,
            totalReviews: $totalReviews,
            positiveReviews: $positiveReviews,
            negativeReviews: $negativeReviews,
            neutralReviews: $neutralReviews,
            averageRating: round($averageRating, 2),
            satisfactionRate: round($satisfactionRate, 2),
            score: round($score, 2),
        );
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            branchId: $data['branch_id'],
            totalReviews: $data['total_reviews'] ?? 0,
            positiveReviews: $data['positive_reviews'] ?? 0,
            negativeReviews: $data['negative_reviews'] ?? 0,
            neutralReviews: $data['neutral_reviews'] ?? 0,
            averageRating: $data['average_rating'] ?? 0,
            satisfactionRate: $data['satisfaction_rate'] ?? 0,
            score: $data['score'] ?? 0,
            ratingDistribution: $data['rating_distribution'] ?? [],
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
            'positive_reviews' => $this->positiveReviews,
            'negative_reviews' => $this->negativeReviews,
            'neutral_reviews' => $this->neutralReviews,
            'average_rating' => $this->averageRating,
            'satisfaction_rate' => $this->satisfactionRate,
            'score' => $this->score,
            'rating_distribution' => $this->ratingDistribution,
        ];
    }
}
