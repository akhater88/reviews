<?php

namespace App\DataTransferObjects\InternalCompetition;

class EmployeeMentionData
{
    public function __construct(
        public readonly string $employeeName,
        public readonly string $sentiment, // positive, negative, neutral
        public readonly ?string $reviewText = null,
        public readonly ?string $reviewId = null,
        public readonly ?\DateTimeInterface $reviewDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employeeName: $data['employee_name'] ?? $data['name'] ?? '',
            sentiment: $data['sentiment'] ?? 'neutral',
            reviewText: $data['review_text'] ?? $data['text'] ?? null,
            reviewId: $data['review_id'] ?? null,
            reviewDate: isset($data['review_date'])
                ? (is_string($data['review_date'])
                    ? new \DateTime($data['review_date'])
                    : $data['review_date'])
                : null,
        );
    }

    public function isPositive(): bool
    {
        return strtolower($this->sentiment) === 'positive';
    }

    public function isNegative(): bool
    {
        return strtolower($this->sentiment) === 'negative';
    }

    public function isNeutral(): bool
    {
        return strtolower($this->sentiment) === 'neutral';
    }
}
