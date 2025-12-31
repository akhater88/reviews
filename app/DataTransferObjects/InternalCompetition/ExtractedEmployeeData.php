<?php

namespace App\DataTransferObjects\InternalCompetition;

class ExtractedEmployeeData
{
    public function __construct(
        public readonly string $employeeName,
        public readonly string $normalizedName,
        public readonly int $totalMentions,
        public readonly int $positiveMentions,
        public readonly int $negativeMentions,
        public readonly int $neutralMentions,
        public readonly float $score,
        public readonly array $samplePositiveMentions = [],
        public readonly array $sampleNegativeMentions = [],
        public readonly ?\DateTimeInterface $firstMentionAt = null,
        public readonly ?\DateTimeInterface $lastMentionAt = null,
    ) {}

    /**
     * Create from grouped mentions
     */
    public static function fromMentions(
        string $employeeName,
        string $normalizedName,
        array $mentions
    ): self {
        $positive = array_filter($mentions, fn ($m) => $m->isPositive());
        $negative = array_filter($mentions, fn ($m) => $m->isNegative());
        $neutral = array_filter($mentions, fn ($m) => $m->isNeutral());

        $positiveMentions = count($positive);
        $negativeMentions = count($negative);
        $neutralMentions = count($neutral);
        $totalMentions = count($mentions);

        // Calculate score: (positive × 10) + (neutral × 1) - (negative × 5)
        $score = ($positiveMentions * 10) + ($neutralMentions * 1) - ($negativeMentions * 5);

        // Get sample mentions (up to 3 each)
        $samplePositive = array_slice(
            array_map(fn ($m) => $m->reviewText, array_filter($positive, fn ($m) => $m->reviewText)),
            0,
            3
        );
        $sampleNegative = array_slice(
            array_map(fn ($m) => $m->reviewText, array_filter($negative, fn ($m) => $m->reviewText)),
            0,
            3
        );

        // Get date range
        $dates = array_filter(array_map(fn ($m) => $m->reviewDate, $mentions));
        $firstMention = !empty($dates) ? min($dates) : null;
        $lastMention = !empty($dates) ? max($dates) : null;

        return new self(
            employeeName: $employeeName,
            normalizedName: $normalizedName,
            totalMentions: $totalMentions,
            positiveMentions: $positiveMentions,
            negativeMentions: $negativeMentions,
            neutralMentions: $neutralMentions,
            score: $score,
            samplePositiveMentions: array_values($samplePositive),
            sampleNegativeMentions: array_values($sampleNegative),
            firstMentionAt: $firstMention,
            lastMentionAt: $lastMention,
        );
    }

    /**
     * Create from analysis result employee data
     */
    public static function fromAnalysisData(array $data, string $normalizedName): self
    {
        $positiveMentions = $data['positive_mentions'] ?? $data['positive'] ?? 0;
        $negativeMentions = $data['negative_mentions'] ?? $data['negative'] ?? 0;
        $neutralMentions = $data['neutral_mentions'] ?? $data['neutral'] ?? 0;
        $totalMentions = $data['total_mentions'] ?? $data['mentions']
            ?? ($positiveMentions + $negativeMentions + $neutralMentions);

        // Calculate score
        $score = ($positiveMentions * 10) + ($neutralMentions * 1) - ($negativeMentions * 5);

        return new self(
            employeeName: $data['name'] ?? $data['employee_name'] ?? '',
            normalizedName: $normalizedName,
            totalMentions: (int) $totalMentions,
            positiveMentions: (int) $positiveMentions,
            negativeMentions: (int) $negativeMentions,
            neutralMentions: (int) $neutralMentions,
            score: (float) $score,
            samplePositiveMentions: $data['sample_positive'] ?? $data['positive_samples'] ?? [],
            sampleNegativeMentions: $data['sample_negative'] ?? $data['negative_samples'] ?? [],
            firstMentionAt: isset($data['first_mention']) ? new \DateTime($data['first_mention']) : null,
            lastMentionAt: isset($data['last_mention']) ? new \DateTime($data['last_mention']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'employee_name' => $this->employeeName,
            'normalized_name' => $this->normalizedName,
            'total_mentions' => $this->totalMentions,
            'positive_mentions' => $this->positiveMentions,
            'negative_mentions' => $this->negativeMentions,
            'neutral_mentions' => $this->neutralMentions,
            'score' => $this->score,
            'sample_positive_mentions' => $this->samplePositiveMentions,
            'sample_negative_mentions' => $this->sampleNegativeMentions,
            'first_mention_at' => $this->firstMentionAt?->format('Y-m-d'),
            'last_mention_at' => $this->lastMentionAt?->format('Y-m-d'),
        ];
    }

    public function getPositivePercentage(): float
    {
        return $this->totalMentions > 0
            ? round(($this->positiveMentions / $this->totalMentions) * 100, 2)
            : 0;
    }

    public function getNegativePercentage(): float
    {
        return $this->totalMentions > 0
            ? round(($this->negativeMentions / $this->totalMentions) * 100, 2)
            : 0;
    }
}
