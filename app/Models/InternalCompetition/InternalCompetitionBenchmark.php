<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\BenchmarkPeriodType;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionBenchmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'tenant_id',
        'branch_id',
        'period_type',
        'period_start',
        'period_end',
        'metrics',
        'calculated_at',
    ];

    protected $casts = [
        'period_type' => BenchmarkPeriodType::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'metrics' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeDuringCompetition($query)
    {
        return $query->where('period_type', BenchmarkPeriodType::DURING_COMPETITION);
    }

    public function scopeBeforeCompetition($query)
    {
        return $query->where('period_type', BenchmarkPeriodType::BEFORE_COMPETITION);
    }

    public function getMetric(string $key, $default = null)
    {
        return $this->metrics[$key] ?? $default;
    }

    /**
     * Compare with another benchmark and return differences
     */
    public function compareWith(self $other): array
    {
        $comparison = [];
        $metricsToCompare = [
            'average_rating' => ['label' => 'متوسط التقييم', 'higher_is_better' => true],
            'total_reviews' => ['label' => 'عدد المراجعات', 'higher_is_better' => true],
            'positive_percentage' => ['label' => 'نسبة الإيجابية', 'higher_is_better' => true],
            'response_time_avg_hours' => ['label' => 'وقت الاستجابة', 'higher_is_better' => false],
            'employee_mentions_positive' => ['label' => 'ذكر الموظفين الإيجابي', 'higher_is_better' => true],
        ];

        foreach ($metricsToCompare as $key => $config) {
            $before = $other->getMetric($key, 0);
            $during = $this->getMetric($key, 0);
            $change = $during - $before;

            $comparison[$key] = [
                'label' => $config['label'],
                'before' => $before,
                'during' => $during,
                'change' => $change,
                'change_percentage' => $before > 0 ? round(($change / $before) * 100, 2) : null,
                'is_improvement' => $config['higher_is_better'] ? $change > 0 : $change < 0,
            ];
        }

        return $comparison;
    }
}
