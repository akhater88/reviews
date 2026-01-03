<?php

namespace App\DataTransferObjects\InternalCompetition;

class BenchmarkMetricsData
{
    public function __construct(
        public readonly float $averageRating,
        public readonly int $totalReviews,
        public readonly int $positiveReviews,
        public readonly int $negativeReviews,
        public readonly int $neutralReviews,
        public readonly float $positivePercentage,
        public readonly float $responseTimeAvgHours,
        public readonly float $replyRate,
        public readonly int $employeeMentionsPositive,
        public readonly int $employeeMentionsNegative,
        public readonly int $employeeMentionsTotal,
        public readonly ?float $averageReviewLength = null,
        public readonly ?int $fiveStarCount = null,
        public readonly ?int $oneStarCount = null,
        public readonly int $foodTastePositive = 0,
        public readonly int $foodTasteNegative = 0,
        public readonly int $foodTasteTotal = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        $totalReviews = $data['total_reviews'] ?? 0;
        $positiveReviews = $data['positive_reviews'] ?? 0;

        return new self(
            averageRating: (float) ($data['average_rating'] ?? 0),
            totalReviews: (int) $totalReviews,
            positiveReviews: (int) $positiveReviews,
            negativeReviews: (int) ($data['negative_reviews'] ?? 0),
            neutralReviews: (int) ($data['neutral_reviews'] ?? 0),
            positivePercentage: $totalReviews > 0
                ? round(($positiveReviews / $totalReviews) * 100, 2)
                : 0,
            responseTimeAvgHours: (float) ($data['response_time_avg_hours'] ?? 0),
            replyRate: (float) ($data['reply_rate'] ?? 0),
            employeeMentionsPositive: (int) ($data['employee_mentions_positive'] ?? 0),
            employeeMentionsNegative: (int) ($data['employee_mentions_negative'] ?? 0),
            employeeMentionsTotal: (int) ($data['employee_mentions_total'] ?? 0),
            averageReviewLength: isset($data['average_review_length'])
                ? (float) $data['average_review_length']
                : null,
            fiveStarCount: isset($data['five_star_count'])
                ? (int) $data['five_star_count']
                : null,
            oneStarCount: isset($data['one_star_count'])
                ? (int) $data['one_star_count']
                : null,
            foodTastePositive: (int) ($data['food_taste_positive'] ?? 0),
            foodTasteNegative: (int) ($data['food_taste_negative'] ?? 0),
            foodTasteTotal: (int) ($data['food_taste_total'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'average_rating' => $this->averageRating,
            'total_reviews' => $this->totalReviews,
            'positive_reviews' => $this->positiveReviews,
            'negative_reviews' => $this->negativeReviews,
            'neutral_reviews' => $this->neutralReviews,
            'positive_percentage' => $this->positivePercentage,
            'response_time_avg_hours' => $this->responseTimeAvgHours,
            'reply_rate' => $this->replyRate,
            'employee_mentions_positive' => $this->employeeMentionsPositive,
            'employee_mentions_negative' => $this->employeeMentionsNegative,
            'employee_mentions_total' => $this->employeeMentionsTotal,
            'average_review_length' => $this->averageReviewLength,
            'five_star_count' => $this->fiveStarCount,
            'one_star_count' => $this->oneStarCount,
            'food_taste_positive' => $this->foodTastePositive,
            'food_taste_negative' => $this->foodTasteNegative,
            'food_taste_total' => $this->foodTasteTotal,
        ];
    }

    public static function empty(): self
    {
        return new self(
            averageRating: 0,
            totalReviews: 0,
            positiveReviews: 0,
            negativeReviews: 0,
            neutralReviews: 0,
            positivePercentage: 0,
            responseTimeAvgHours: 0,
            replyRate: 0,
            employeeMentionsPositive: 0,
            employeeMentionsNegative: 0,
            employeeMentionsTotal: 0,
            foodTastePositive: 0,
            foodTasteNegative: 0,
            foodTasteTotal: 0,
        );
    }
}
