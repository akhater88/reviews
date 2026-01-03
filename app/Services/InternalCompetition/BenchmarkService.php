<?php

namespace App\Services\InternalCompetition;

use App\DataTransferObjects\InternalCompetition\BenchmarkComparisonData;
use App\DataTransferObjects\InternalCompetition\BenchmarkMetricsData;
use App\Enums\InternalCompetition\BenchmarkPeriodType;
use App\Models\Branch;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBenchmark;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BenchmarkService
{
    /**
     * Metrics configuration for comparison
     */
    protected array $metricsConfig = [
        'average_rating' => [
            'label' => 'متوسط التقييم',
            'higher_is_better' => true,
        ],
        'total_reviews' => [
            'label' => 'عدد المراجعات',
            'higher_is_better' => true,
        ],
        'positive_percentage' => [
            'label' => 'نسبة الإيجابية',
            'higher_is_better' => true,
        ],
        'response_time_avg_hours' => [
            'label' => 'متوسط وقت الاستجابة (ساعات)',
            'higher_is_better' => false,
        ],
        'reply_rate' => [
            'label' => 'معدل الرد',
            'higher_is_better' => true,
        ],
        'employee_mentions_positive' => [
            'label' => 'ذكر الموظفين الإيجابي',
            'higher_is_better' => true,
        ],
        'food_taste_positive' => [
            'label' => 'الطعام/الطعم الإيجابي',
            'higher_is_better' => true,
        ],
    ];

    /**
     * Calculate all benchmarks for a competition
     */
    public function calculateAllBenchmarks(InternalCompetition $competition): array
    {
        $results = [
            'competition_id' => $competition->id,
            'tenants' => [],
            'branches' => [],
            'overall' => null,
            'calculated_at' => now(),
        ];

        // Get all participating branches grouped by tenant
        $branchesByTenant = $competition->activeBranches()
            ->with('branch')
            ->get()
            ->groupBy('tenant_id');

        foreach ($branchesByTenant as $tenantId => $participatingBranches) {
            $branchIds = $participatingBranches->pluck('branch_id')->toArray();

            // Calculate tenant-level benchmark
            $tenantBenchmark = $this->calculateTenantBenchmark(
                $competition,
                $tenantId,
                $branchIds
            );
            $results['tenants'][$tenantId] = $tenantBenchmark;

            // Calculate branch-level benchmarks
            foreach ($branchIds as $branchId) {
                $branchBenchmark = $this->calculateBranchBenchmark(
                    $competition,
                    $tenantId,
                    $branchId
                );
                $results['branches'][$branchId] = $branchBenchmark;
            }
        }

        // Calculate overall benchmark (all branches combined)
        $allBranchIds = $competition->activeBranches()->pluck('branch_id')->toArray();
        $results['overall'] = $this->calculateOverallBenchmark($competition, $allBranchIds);

        Log::info('Benchmarks calculated for competition', [
            'competition_id' => $competition->id,
            'tenants_count' => count($results['tenants']),
            'branches_count' => count($results['branches']),
        ]);

        return $results;
    }

    /**
     * Calculate benchmark for a specific tenant
     */
    public function calculateTenantBenchmark(
        InternalCompetition $competition,
        int $tenantId,
        array $branchIds
    ): array {
        // Calculate "before" period metrics
        $beforeMetrics = $this->calculatePeriodMetrics(
            $branchIds,
            $this->getBeforePeriodStart($competition),
            $this->getBeforePeriodEnd($competition)
        );

        // Calculate "during" period metrics
        $duringMetrics = $this->calculatePeriodMetrics(
            $branchIds,
            $competition->start_date,
            min($competition->end_date, now())
        );

        // Save benchmarks
        $this->saveBenchmark(
            $competition,
            $tenantId,
            null,
            BenchmarkPeriodType::BEFORE_COMPETITION,
            $this->getBeforePeriodStart($competition),
            $this->getBeforePeriodEnd($competition),
            $beforeMetrics
        );

        $this->saveBenchmark(
            $competition,
            $tenantId,
            null,
            BenchmarkPeriodType::DURING_COMPETITION,
            $competition->start_date,
            min($competition->end_date, now()),
            $duringMetrics
        );

        // Generate comparison
        return $this->generateComparison($beforeMetrics, $duringMetrics);
    }

    /**
     * Calculate benchmark for a specific branch
     */
    public function calculateBranchBenchmark(
        InternalCompetition $competition,
        int $tenantId,
        int $branchId
    ): array {
        // Calculate "before" period metrics
        $beforeMetrics = $this->calculatePeriodMetrics(
            [$branchId],
            $this->getBeforePeriodStart($competition),
            $this->getBeforePeriodEnd($competition)
        );

        // Calculate "during" period metrics
        $duringMetrics = $this->calculatePeriodMetrics(
            [$branchId],
            $competition->start_date,
            min($competition->end_date, now())
        );

        // Save benchmarks
        $this->saveBenchmark(
            $competition,
            $tenantId,
            $branchId,
            BenchmarkPeriodType::BEFORE_COMPETITION,
            $this->getBeforePeriodStart($competition),
            $this->getBeforePeriodEnd($competition),
            $beforeMetrics
        );

        $this->saveBenchmark(
            $competition,
            $tenantId,
            $branchId,
            BenchmarkPeriodType::DURING_COMPETITION,
            $competition->start_date,
            min($competition->end_date, now()),
            $duringMetrics
        );

        // Generate comparison
        return $this->generateComparison($beforeMetrics, $duringMetrics);
    }

    /**
     * Calculate overall benchmark for all branches
     */
    public function calculateOverallBenchmark(
        InternalCompetition $competition,
        array $branchIds
    ): array {
        $beforeMetrics = $this->calculatePeriodMetrics(
            $branchIds,
            $this->getBeforePeriodStart($competition),
            $this->getBeforePeriodEnd($competition)
        );

        $duringMetrics = $this->calculatePeriodMetrics(
            $branchIds,
            $competition->start_date,
            min($competition->end_date, now())
        );

        return $this->generateComparison($beforeMetrics, $duringMetrics);
    }

    /**
     * Get "before" period start date
     * Uses same duration as competition period, ending when competition starts
     */
    protected function getBeforePeriodStart(InternalCompetition $competition): Carbon
    {
        $competitionDays = $competition->start_date->diffInDays($competition->end_date);
        return $competition->start_date->copy()->subDays($competitionDays);
    }

    /**
     * Get "before" period end date
     */
    protected function getBeforePeriodEnd(InternalCompetition $competition): Carbon
    {
        return $competition->start_date->copy()->subDay();
    }

    /**
     * Calculate metrics for a specific period
     */
    protected function calculatePeriodMetrics(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): BenchmarkMetricsData {
        if (empty($branchIds)) {
            return BenchmarkMetricsData::empty();
        }

        // Get review statistics
        $reviewStats = Review::whereIn('branch_id', $branchIds)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->select([
                DB::raw('COUNT(*) as total_reviews'),
                DB::raw('AVG(rating) as average_rating'),
                DB::raw('SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END) as positive_reviews'),
                DB::raw('SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END) as negative_reviews'),
                DB::raw('SUM(CASE WHEN sentiment = "neutral" OR sentiment IS NULL THEN 1 ELSE 0 END) as neutral_reviews'),
                DB::raw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star_count'),
                DB::raw('SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star_count'),
                DB::raw('AVG(CHAR_LENGTH(text)) as average_review_length'),
            ])
            ->first();

        // Get response time statistics
        $responseStats = $this->calculateResponseTimeStats($branchIds, $periodStart, $periodEnd);

        // Get employee mention statistics
        $employeeStats = $this->calculateEmployeeMentionStats($branchIds, $periodStart, $periodEnd);

        // Get food/taste mention statistics
        $foodTasteStats = $this->calculateFoodTasteStats($branchIds, $periodStart, $periodEnd);

        return BenchmarkMetricsData::fromArray([
            'average_rating' => $reviewStats->average_rating ?? 0,
            'total_reviews' => $reviewStats->total_reviews ?? 0,
            'positive_reviews' => $reviewStats->positive_reviews ?? 0,
            'negative_reviews' => $reviewStats->negative_reviews ?? 0,
            'neutral_reviews' => $reviewStats->neutral_reviews ?? 0,
            'response_time_avg_hours' => $responseStats['avg_hours'] ?? 0,
            'reply_rate' => $responseStats['reply_rate'] ?? 0,
            'employee_mentions_positive' => $employeeStats['positive'] ?? 0,
            'employee_mentions_negative' => $employeeStats['negative'] ?? 0,
            'employee_mentions_total' => $employeeStats['total'] ?? 0,
            'average_review_length' => $reviewStats->average_review_length,
            'five_star_count' => $reviewStats->five_star_count,
            'one_star_count' => $reviewStats->one_star_count,
            'food_taste_positive' => $foodTasteStats['positive'] ?? 0,
            'food_taste_negative' => $foodTasteStats['negative'] ?? 0,
            'food_taste_total' => $foodTasteStats['total'] ?? 0,
        ]);
    }

    /**
     * Calculate response time statistics
     */
    protected function calculateResponseTimeStats(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $stats = Review::whereIn('branch_id', $branchIds)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN owner_reply IS NOT NULL AND owner_reply != "" THEN 1 ELSE 0 END) as replied'),
                DB::raw('AVG(CASE WHEN owner_reply_date IS NOT NULL THEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) ELSE NULL END) as avg_hours'),
            ])
            ->first();

        $total = $stats->total ?? 0;
        $replied = $stats->replied ?? 0;

        return [
            'avg_hours' => round($stats->avg_hours ?? 0, 2),
            'reply_rate' => $total > 0 ? round(($replied / $total) * 100, 2) : 0,
            'total' => $total,
            'replied' => $replied,
        ];
    }

    /**
     * Calculate employee mention statistics
     */
    protected function calculateEmployeeMentionStats(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // Check if the column exists before querying
        if (!DB::getSchemaBuilder()->hasColumn('reviews', 'employee_mentioned')) {
            return [
                'total' => 0,
                'positive' => 0,
                'negative' => 0,
            ];
        }

        $stats = Review::whereIn('branch_id', $branchIds)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('employee_mentioned')
            ->where('employee_mentioned', '!=', '')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END) as positive'),
                DB::raw('SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END) as negative'),
            ])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'positive' => $stats->positive ?? 0,
            'negative' => $stats->negative ?? 0,
        ];
    }

    /**
     * Calculate food/taste mention statistics
     */
    protected function calculateFoodTasteStats(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // Query reviews that have 'food' in their categories JSON array
        $stats = Review::whereIn('branch_id', $branchIds)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('categories')
            ->whereRaw('JSON_CONTAINS(categories, \'"food"\')')
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END) as positive'),
                DB::raw('SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END) as negative'),
            ])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'positive' => $stats->positive ?? 0,
            'negative' => $stats->negative ?? 0,
        ];
    }

    /**
     * Generate comparison between two periods
     */
    protected function generateComparison(
        BenchmarkMetricsData $before,
        BenchmarkMetricsData $during
    ): array {
        $comparisons = [];

        foreach ($this->metricsConfig as $key => $config) {
            $beforeValue = $this->getMetricValue($before, $key);
            $duringValue = $this->getMetricValue($during, $key);

            $comparisons[$key] = BenchmarkComparisonData::calculate(
                $key,
                $config['label'],
                $beforeValue,
                $duringValue,
                $config['higher_is_better']
            )->toArray();
        }

        // Calculate overall improvement score
        $improvements = array_filter($comparisons, fn ($c) => $c['is_improvement']);
        $overallScore = count($comparisons) > 0
            ? round((count($improvements) / count($comparisons)) * 100, 2)
            : 0;

        return [
            'before' => $before->toArray(),
            'during' => $during->toArray(),
            'comparisons' => $comparisons,
            'overall_improvement_score' => $overallScore,
            'improvements_count' => count($improvements),
            'total_metrics' => count($comparisons),
        ];
    }

    /**
     * Get metric value from BenchmarkMetricsData
     */
    protected function getMetricValue(BenchmarkMetricsData $data, string $key): float
    {
        return match ($key) {
            'average_rating' => $data->averageRating,
            'total_reviews' => $data->totalReviews,
            'positive_percentage' => $data->positivePercentage,
            'response_time_avg_hours' => $data->responseTimeAvgHours,
            'reply_rate' => $data->replyRate,
            'employee_mentions_positive' => $data->employeeMentionsPositive,
            'food_taste_positive' => $data->foodTastePositive,
            default => 0,
        };
    }

    /**
     * Save benchmark to database
     */
    protected function saveBenchmark(
        InternalCompetition $competition,
        int $tenantId,
        ?int $branchId,
        BenchmarkPeriodType $periodType,
        Carbon $periodStart,
        Carbon $periodEnd,
        BenchmarkMetricsData $metrics
    ): InternalCompetitionBenchmark {
        return InternalCompetitionBenchmark::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'period_type' => $periodType,
            ],
            [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'metrics' => $metrics->toArray(),
                'calculated_at' => now(),
            ]
        );
    }

    /**
     * Get benchmark comparison for a tenant
     */
    public function getTenantBenchmark(
        InternalCompetition $competition,
        int $tenantId
    ): ?array {
        $before = InternalCompetitionBenchmark::where('competition_id', $competition->id)
            ->where('tenant_id', $tenantId)
            ->whereNull('branch_id')
            ->where('period_type', BenchmarkPeriodType::BEFORE_COMPETITION)
            ->first();

        $during = InternalCompetitionBenchmark::where('competition_id', $competition->id)
            ->where('tenant_id', $tenantId)
            ->whereNull('branch_id')
            ->where('period_type', BenchmarkPeriodType::DURING_COMPETITION)
            ->first();

        if (!$before || !$during) {
            return null;
        }

        return $this->generateComparison(
            BenchmarkMetricsData::fromArray($before->metrics),
            BenchmarkMetricsData::fromArray($during->metrics)
        );
    }

    /**
     * Get benchmark comparison for a branch
     */
    public function getBranchBenchmark(
        InternalCompetition $competition,
        int $branchId
    ): ?array {
        $before = InternalCompetitionBenchmark::where('competition_id', $competition->id)
            ->where('branch_id', $branchId)
            ->where('period_type', BenchmarkPeriodType::BEFORE_COMPETITION)
            ->first();

        $during = InternalCompetitionBenchmark::where('competition_id', $competition->id)
            ->where('branch_id', $branchId)
            ->where('period_type', BenchmarkPeriodType::DURING_COMPETITION)
            ->first();

        if (!$before || !$during) {
            return null;
        }

        return $this->generateComparison(
            BenchmarkMetricsData::fromArray($before->metrics),
            BenchmarkMetricsData::fromArray($during->metrics)
        );
    }

    /**
     * Get all branch benchmarks for a tenant
     */
    public function getTenantBranchesBenchmarks(
        InternalCompetition $competition,
        int $tenantId
    ): Collection {
        $branchIds = $competition->activeBranches()
            ->where('tenant_id', $tenantId)
            ->pluck('branch_id');

        $benchmarks = collect();

        foreach ($branchIds as $branchId) {
            $benchmark = $this->getBranchBenchmark($competition, $branchId);
            if ($benchmark) {
                $branch = Branch::find($branchId);
                $benchmarks->push([
                    'branch_id' => $branchId,
                    'branch_name' => $branch?->name,
                    'benchmark' => $benchmark,
                ]);
            }
        }

        return $benchmarks;
    }

    /**
     * Get ROI summary for a competition
     */
    public function getROISummary(InternalCompetition $competition): array
    {
        $allBranchIds = $competition->activeBranches()->pluck('branch_id')->toArray();
        $overall = $this->calculateOverallBenchmark($competition, $allBranchIds);

        // Identify top improvements
        $topImprovements = collect($overall['comparisons'])
            ->filter(fn ($c) => $c['is_improvement'])
            ->sortByDesc('change_percentage')
            ->take(3)
            ->values()
            ->toArray();

        // Identify areas needing attention
        $needsAttention = collect($overall['comparisons'])
            ->filter(fn ($c) => !$c['is_improvement'] && abs($c['change_percentage']) > 5)
            ->sortBy('change_percentage')
            ->take(3)
            ->values()
            ->toArray();

        return [
            'overall_improvement_score' => $overall['overall_improvement_score'],
            'total_metrics' => $overall['total_metrics'],
            'improvements_count' => $overall['improvements_count'],
            'top_improvements' => $topImprovements,
            'needs_attention' => $needsAttention,
            'summary' => $this->generateROISummaryText($overall),
        ];
    }

    /**
     * Generate human-readable ROI summary
     */
    protected function generateROISummaryText(array $overall): string
    {
        $score = $overall['overall_improvement_score'];
        $improvements = $overall['improvements_count'];
        $total = $overall['total_metrics'];

        if ($score >= 80) {
            return "أداء ممتاز! تحسن في {$improvements} من {$total} مقاييس. المسابقة حققت نتائج رائعة.";
        } elseif ($score >= 50) {
            return "أداء جيد! تحسن في {$improvements} من {$total} مقاييس. هناك مجال للتحسين.";
        } elseif ($score > 0) {
            return "أداء متوسط. تحسن في {$improvements} من {$total} مقاييس فقط. يُنصح بمراجعة الاستراتيجية.";
        } else {
            return "لم يتم تسجيل تحسن ملحوظ. قد تحتاج لإعادة تقييم أهداف المسابقة.";
        }
    }

    /**
     * Recalculate all benchmarks (force refresh)
     */
    public function recalculateBenchmarks(InternalCompetition $competition): array
    {
        // Delete existing benchmarks
        InternalCompetitionBenchmark::where('competition_id', $competition->id)->delete();

        // Recalculate
        return $this->calculateAllBenchmarks($competition);
    }
}
