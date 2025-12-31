<?php

namespace App\DataTransferObjects\InternalCompetition;

class BenchmarkComparisonData
{
    public function __construct(
        public readonly string $metricKey,
        public readonly string $metricLabel,
        public readonly float $beforeValue,
        public readonly float $duringValue,
        public readonly float $change,
        public readonly ?float $changePercentage,
        public readonly bool $isImprovement,
        public readonly string $trend, // up, down, stable
        public readonly ?string $insight = null,
    ) {}

    public static function calculate(
        string $metricKey,
        string $metricLabel,
        float $beforeValue,
        float $duringValue,
        bool $higherIsBetter = true
    ): self {
        $change = round($duringValue - $beforeValue, 2);
        $changePercentage = $beforeValue > 0
            ? round(($change / $beforeValue) * 100, 2)
            : ($duringValue > 0 ? 100 : 0);

        $isImprovement = $higherIsBetter
            ? $change > 0
            : $change < 0;

        $trend = 'stable';
        if (abs($changePercentage) >= 5) {
            $trend = $change > 0 ? 'up' : 'down';
        }

        // Generate insight
        $insight = self::generateInsight(
            $metricKey,
            $beforeValue,
            $duringValue,
            $changePercentage,
            $isImprovement
        );

        return new self(
            metricKey: $metricKey,
            metricLabel: $metricLabel,
            beforeValue: $beforeValue,
            duringValue: $duringValue,
            change: $change,
            changePercentage: $changePercentage,
            isImprovement: $isImprovement,
            trend: $trend,
            insight: $insight,
        );
    }

    protected static function generateInsight(
        string $metricKey,
        float $before,
        float $during,
        float $changePercentage,
        bool $isImprovement
    ): ?string {
        if (abs($changePercentage) < 5) {
            return 'لا تغيير ملحوظ';
        }

        $absChange = abs($changePercentage);

        return match ($metricKey) {
            'average_rating' => $isImprovement
                ? "تحسن التقييم بنسبة {$absChange}%"
                : "انخفض التقييم بنسبة {$absChange}%",
            'total_reviews' => $isImprovement
                ? "زيادة في عدد المراجعات بنسبة {$absChange}%"
                : "انخفاض في عدد المراجعات بنسبة {$absChange}%",
            'positive_percentage' => $isImprovement
                ? "تحسن في نسبة الإيجابية بنسبة {$absChange}%"
                : "تراجع في نسبة الإيجابية بنسبة {$absChange}%",
            'response_time_avg_hours' => $isImprovement
                ? "تحسن في سرعة الاستجابة بنسبة {$absChange}%"
                : "تراجع في سرعة الاستجابة بنسبة {$absChange}%",
            'reply_rate' => $isImprovement
                ? "زيادة في معدل الرد بنسبة {$absChange}%"
                : "انخفاض في معدل الرد بنسبة {$absChange}%",
            'employee_mentions_positive' => $isImprovement
                ? "زيادة في ذكر الموظفين الإيجابي بنسبة {$absChange}%"
                : "انخفاض في ذكر الموظفين الإيجابي بنسبة {$absChange}%",
            default => ($isImprovement ? 'تحسن' : 'تراجع') . " بنسبة {$absChange}%",
        };
    }

    public function toArray(): array
    {
        return [
            'metric_key' => $this->metricKey,
            'metric_label' => $this->metricLabel,
            'before_value' => $this->beforeValue,
            'during_value' => $this->duringValue,
            'change' => $this->change,
            'change_percentage' => $this->changePercentage,
            'is_improvement' => $this->isImprovement,
            'trend' => $this->trend,
            'insight' => $this->insight,
        ];
    }

    public function getTrendIcon(): string
    {
        return match ($this->trend) {
            'up' => '📈',
            'down' => '📉',
            default => '➡️',
        };
    }

    public function getTrendColor(): string
    {
        if ($this->trend === 'stable') {
            return 'gray';
        }
        return $this->isImprovement ? 'success' : 'danger';
    }
}
