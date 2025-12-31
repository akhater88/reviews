<?php

namespace App\Services\InternalCompetition;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\DataTransferObjects\InternalCompetition\EmployeeMentionData;
use App\DataTransferObjects\InternalCompetition\ExtractedEmployeeData;
use App\Enums\AnalysisType;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Exceptions\InternalCompetition\EmployeeExtractionException;
use App\Models\AnalysisResult;
use App\Models\Branch;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\InternalCompetition\InternalCompetitionEmployee;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeExtractionService implements ScoreCalculatorInterface
{
    protected const SIMILARITY_THRESHOLD = 80.0;
    protected const MAX_SAMPLE_MENTIONS = 3;

    public function __construct(
        protected ArabicNameNormalizer $nameNormalizer
    ) {}

    /**
     * Get the metric type this calculator handles
     */
    public function getMetricType(): string
    {
        return CompetitionMetric::EMPLOYEE_MENTIONS->value;
    }

    /**
     * Calculate score for a specific branch (implements ScoreCalculatorInterface)
     */
    public function calculateScore(
        InternalCompetition $competition,
        InternalCompetitionBranch $participant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $topEmployee = $this->calculateForBranch(
            $competition,
            $participant->branch_id,
            $periodStart,
            $periodEnd
        );

        return $topEmployee?->score ?? 0;
    }

    /**
     * Calculate scores for all participants in a competition (implements ScoreCalculatorInterface)
     */
    public function calculateScoresForCompetition(
        InternalCompetition $competition,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $employees = $this->calculateForAllBranches($competition, $periodStart, $periodEnd);

        // Return best employee score per branch
        $scores = [];
        foreach ($employees->groupBy('branch_id') as $branchId => $branchEmployees) {
            $scores[$branchId] = $branchEmployees->max('score') ?? 0;
        }

        return $scores;
    }

    /**
     * Calculate score for a single branch
     */
    public function calculateForBranch(
        InternalCompetition $competition,
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): ?InternalCompetitionEmployee {
        try {
            $employees = $this->extractAndSaveEmployees(
                $competition,
                $branchId,
                $periodStart,
                $periodEnd
            );

            // Return the top scoring employee for this branch
            return $employees->sortByDesc('score')->first();

        } catch (\Exception $e) {
            Log::error('Failed to calculate employee scores for branch', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate scores for all branches in a competition
     */
    public function calculateForAllBranches(
        InternalCompetition $competition,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        $allEmployees = collect();

        // Get all active branches in the competition
        $branchIds = $competition->activeBranches()->pluck('branch_id');

        foreach ($branchIds as $branchId) {
            try {
                $employees = $this->extractAndSaveEmployees(
                    $competition,
                    $branchId,
                    $periodStart,
                    $periodEnd
                );
                $allEmployees = $allEmployees->merge($employees);
            } catch (\Exception $e) {
                Log::warning('Failed to extract employees for branch', [
                    'competition_id' => $competition->id,
                    'branch_id' => $branchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update rankings across all employees
        $this->updateRankings($competition);

        Log::info('Employee extraction completed for competition', [
            'competition_id' => $competition->id,
            'total_employees' => $allEmployees->count(),
            'branches_processed' => $branchIds->count(),
        ]);

        return $allEmployees;
    }

    /**
     * Get score breakdown for a branch
     */
    public function getScoreBreakdown(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $employees = $this->extractEmployeesFromAnalysis($branchId, $periodStart, $periodEnd);

        return [
            'total_employees' => count($employees),
            'employees' => array_map(fn ($e) => $e->toArray(), $employees),
            'formula' => '(إيجابي × 10) + (محايد × 1) - (سلبي × 5)',
        ];
    }

    /**
     * Extract employees and save to database
     */
    public function extractAndSaveEmployees(
        InternalCompetition $competition,
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        $branch = Branch::find($branchId);
        if (!$branch) {
            return collect();
        }

        // Extract employee data
        $extractedEmployees = $this->extractEmployeesFromAnalysis(
            $branchId,
            $periodStart,
            $periodEnd
        );

        if (empty($extractedEmployees)) {
            // Try fallback extraction from reviews
            $extractedEmployees = $this->extractFromReviewsDirectly(
                $branchId,
                $periodStart,
                $periodEnd
            );
        }

        $savedEmployees = collect();

        foreach ($extractedEmployees as $employeeData) {
            try {
                $employee = $this->saveOrUpdateEmployee(
                    $competition,
                    $branch,
                    $employeeData
                );
                $savedEmployees->push($employee);
            } catch (\Exception $e) {
                Log::warning('Failed to save employee', [
                    'employee_name' => $employeeData->employeeName,
                    'branch_id' => $branchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $savedEmployees;
    }

    /**
     * Extract employees from analysis_results table
     */
    protected function extractEmployeesFromAnalysis(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // Get employee insights from analysis_results (uses restaurant_id)
        $analysisResults = AnalysisResult::where('restaurant_id', $branchId)
            ->where('analysis_type', AnalysisType::EMPLOYEES_INSIGHTS)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderByDesc('created_at')
            ->get();

        $allEmployeeData = [];

        foreach ($analysisResults as $analysis) {
            $resultData = $analysis->result;

            if (!is_array($resultData)) {
                continue;
            }

            // Extract employees from various possible structures
            $employees = $this->parseEmployeesFromAnalysis($resultData);

            foreach ($employees as $empData) {
                $name = $empData['name'] ?? $empData['employee_name'] ?? null;
                if (!$name) {
                    continue;
                }

                $normalizedName = $this->nameNormalizer->normalize($name);

                // Check if we already have this employee (fuzzy match)
                $existingKey = $this->findExistingEmployeeKey(
                    $allEmployeeData,
                    $normalizedName
                );

                if ($existingKey !== null) {
                    // Merge with existing
                    $allEmployeeData[$existingKey] = $this->mergeEmployeeData(
                        $allEmployeeData[$existingKey],
                        $empData
                    );
                } else {
                    // Add new employee
                    $allEmployeeData[$normalizedName] = ExtractedEmployeeData::fromAnalysisData(
                        $empData,
                        $normalizedName
                    );
                }
            }
        }

        return array_values($allEmployeeData);
    }

    /**
     * Parse employees from analysis result data
     */
    protected function parseEmployeesFromAnalysis(array $resultData): array
    {
        // Try different possible structures
        if (isset($resultData['employees'])) {
            return $resultData['employees'];
        }

        if (isset($resultData['staff'])) {
            return $resultData['staff'];
        }

        if (isset($resultData['team_members'])) {
            return $resultData['team_members'];
        }

        if (isset($resultData['mentioned_employees'])) {
            return $resultData['mentioned_employees'];
        }

        // Check if the result itself is an array of employees
        if (isset($resultData[0]['name']) || isset($resultData[0]['employee_name'])) {
            return $resultData;
        }

        // Try to extract from nested structure
        foreach ($resultData as $key => $value) {
            if (is_array($value) && !empty($value)) {
                if (isset($value[0]['name']) || isset($value[0]['employee_name'])) {
                    return $value;
                }
            }
        }

        return [];
    }

    /**
     * Extract employees directly from reviews (fallback)
     */
    protected function extractFromReviewsDirectly(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // Check if reviews table has employee_mentioned column
        if (!$this->hasEmployeeMentionedColumn()) {
            return [];
        }

        $reviews = Review::where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('employee_mentioned')
            ->where('employee_mentioned', '!=', '')
            ->select(['id', 'employee_mentioned', 'sentiment', 'text', 'review_date'])
            ->get();

        if ($reviews->isEmpty()) {
            return [];
        }

        // Group by normalized employee name
        $employeeMentions = [];

        foreach ($reviews as $review) {
            $name = $review->employee_mentioned;
            $normalizedName = $this->nameNormalizer->normalize($name);

            $mention = new EmployeeMentionData(
                employeeName: $name,
                sentiment: $review->sentiment ?? 'neutral',
                reviewText: $review->text,
                reviewId: (string) $review->id,
                reviewDate: $review->review_date
            );

            // Find existing or create new group
            $existingKey = $this->findExistingMentionKey($employeeMentions, $normalizedName);

            if ($existingKey !== null) {
                $employeeMentions[$existingKey]['mentions'][] = $mention;
            } else {
                $employeeMentions[$normalizedName] = [
                    'name' => $name,
                    'normalized' => $normalizedName,
                    'mentions' => [$mention],
                ];
            }
        }

        // Convert to ExtractedEmployeeData
        $result = [];
        foreach ($employeeMentions as $data) {
            $result[] = ExtractedEmployeeData::fromMentions(
                $data['name'],
                $data['normalized'],
                $data['mentions']
            );
        }

        return $result;
    }

    /**
     * Check if reviews table has employee_mentioned column
     */
    protected function hasEmployeeMentionedColumn(): bool
    {
        return DB::getSchemaBuilder()->hasColumn('reviews', 'employee_mentioned');
    }

    /**
     * Find existing employee key using fuzzy matching
     */
    protected function findExistingEmployeeKey(array $employees, string $normalizedName): ?string
    {
        foreach ($employees as $key => $employee) {
            if ($this->nameNormalizer->areSamePerson($key, $normalizedName, self::SIMILARITY_THRESHOLD)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Find existing mention key using fuzzy matching
     */
    protected function findExistingMentionKey(array $mentions, string $normalizedName): ?string
    {
        foreach ($mentions as $key => $data) {
            if ($this->nameNormalizer->areSamePerson($key, $normalizedName, self::SIMILARITY_THRESHOLD)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Merge employee data from multiple analyses
     */
    protected function mergeEmployeeData(
        ExtractedEmployeeData $existing,
        array $newData
    ): ExtractedEmployeeData {
        $newPositive = $newData['positive_mentions'] ?? $newData['positive'] ?? 0;
        $newNegative = $newData['negative_mentions'] ?? $newData['negative'] ?? 0;
        $newNeutral = $newData['neutral_mentions'] ?? $newData['neutral'] ?? 0;

        $totalPositive = $existing->positiveMentions + $newPositive;
        $totalNegative = $existing->negativeMentions + $newNegative;
        $totalNeutral = $existing->neutralMentions + $newNeutral;
        $totalMentions = $totalPositive + $totalNegative + $totalNeutral;

        // Recalculate score
        $score = ($totalPositive * 10) + ($totalNeutral * 1) - ($totalNegative * 5);

        // Merge sample mentions
        $samplePositive = array_slice(
            array_merge(
                $existing->samplePositiveMentions,
                $newData['sample_positive'] ?? $newData['positive_samples'] ?? []
            ),
            0,
            self::MAX_SAMPLE_MENTIONS
        );

        $sampleNegative = array_slice(
            array_merge(
                $existing->sampleNegativeMentions,
                $newData['sample_negative'] ?? $newData['negative_samples'] ?? []
            ),
            0,
            self::MAX_SAMPLE_MENTIONS
        );

        // Update date range
        $newFirstMention = isset($newData['first_mention'])
            ? new \DateTime($newData['first_mention'])
            : null;
        $newLastMention = isset($newData['last_mention'])
            ? new \DateTime($newData['last_mention'])
            : null;

        $firstMention = $existing->firstMentionAt;
        if ($newFirstMention && (!$firstMention || $newFirstMention < $firstMention)) {
            $firstMention = $newFirstMention;
        }

        $lastMention = $existing->lastMentionAt;
        if ($newLastMention && (!$lastMention || $newLastMention > $lastMention)) {
            $lastMention = $newLastMention;
        }

        return new ExtractedEmployeeData(
            employeeName: $existing->employeeName,
            normalizedName: $existing->normalizedName,
            totalMentions: $totalMentions,
            positiveMentions: $totalPositive,
            negativeMentions: $totalNegative,
            neutralMentions: $totalNeutral,
            score: $score,
            samplePositiveMentions: $samplePositive,
            sampleNegativeMentions: $sampleNegative,
            firstMentionAt: $firstMention,
            lastMentionAt: $lastMention,
        );
    }

    /**
     * Save or update employee record
     */
    protected function saveOrUpdateEmployee(
        InternalCompetition $competition,
        Branch $branch,
        ExtractedEmployeeData $employeeData
    ): InternalCompetitionEmployee {
        // Check for existing employee with similar name
        $existingEmployee = $this->findExistingEmployeeRecord(
            $competition->id,
            $branch->id,
            $employeeData->normalizedName
        );

        if ($existingEmployee) {
            // Update existing record
            $existingEmployee->update([
                'employee_name' => $employeeData->employeeName,
                'total_mentions' => $employeeData->totalMentions,
                'positive_mentions' => $employeeData->positiveMentions,
                'negative_mentions' => $employeeData->negativeMentions,
                'neutral_mentions' => $employeeData->neutralMentions,
                'score' => $employeeData->score,
                'sample_positive_mentions' => $employeeData->samplePositiveMentions,
                'sample_negative_mentions' => $employeeData->sampleNegativeMentions,
                'first_mention_at' => $employeeData->firstMentionAt,
                'last_mention_at' => $employeeData->lastMentionAt,
                'calculated_at' => now(),
            ]);

            return $existingEmployee->fresh();
        }

        // Create new record
        return InternalCompetitionEmployee::create([
            'competition_id' => $competition->id,
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'employee_name' => $employeeData->employeeName,
            'normalized_name' => $employeeData->normalizedName,
            'total_mentions' => $employeeData->totalMentions,
            'positive_mentions' => $employeeData->positiveMentions,
            'negative_mentions' => $employeeData->negativeMentions,
            'neutral_mentions' => $employeeData->neutralMentions,
            'score' => $employeeData->score,
            'sample_positive_mentions' => $employeeData->samplePositiveMentions,
            'sample_negative_mentions' => $employeeData->sampleNegativeMentions,
            'first_mention_at' => $employeeData->firstMentionAt,
            'last_mention_at' => $employeeData->lastMentionAt,
            'is_final' => false,
            'calculated_at' => now(),
        ]);
    }

    /**
     * Find existing employee record with fuzzy name matching
     */
    protected function findExistingEmployeeRecord(
        int $competitionId,
        int $branchId,
        string $normalizedName
    ): ?InternalCompetitionEmployee {
        // First try exact match on normalized name
        $exact = InternalCompetitionEmployee::where('competition_id', $competitionId)
            ->where('branch_id', $branchId)
            ->where('normalized_name', $normalizedName)
            ->first();

        if ($exact) {
            return $exact;
        }

        // Try fuzzy match on all employees for this branch
        $allEmployees = InternalCompetitionEmployee::where('competition_id', $competitionId)
            ->where('branch_id', $branchId)
            ->get();

        foreach ($allEmployees as $employee) {
            if ($this->nameNormalizer->areSamePerson(
                $employee->normalized_name,
                $normalizedName,
                self::SIMILARITY_THRESHOLD
            )) {
                return $employee;
            }
        }

        return null;
    }

    /**
     * Update rankings for all employees in a competition
     */
    public function updateRankings(InternalCompetition $competition): void
    {
        $employees = InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->orderByDesc('score')
            ->get();

        $rank = 1;
        $previousScore = null;
        $sameRankCount = 0;

        foreach ($employees as $employee) {
            // Handle ties
            if ($previousScore !== null && $employee->score == $previousScore) {
                $sameRankCount++;
            } else {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            }

            $employee->update(['rank' => $rank]);
            $previousScore = $employee->score;
        }

        Log::info('Employee rankings updated', [
            'competition_id' => $competition->id,
            'employees_count' => $employees->count(),
        ]);
    }

    /**
     * Finalize scores at competition end
     */
    public function finalizeScores(InternalCompetition $competition): void
    {
        InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->update(['is_final' => true]);

        Log::info('Employee scores finalized', [
            'competition_id' => $competition->id,
        ]);
    }

    /**
     * Get top performers (employees)
     */
    public function getTopPerformers(InternalCompetition $competition, int $limit = 3): Collection
    {
        return InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->with(['branch', 'tenant'])
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }

    /**
     * Get employees for a specific branch
     */
    public function getBranchEmployees(InternalCompetition $competition, int $branchId): Collection
    {
        return InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->where('branch_id', $branchId)
            ->orderByDesc('score')
            ->get();
    }

    /**
     * Get employee leaderboard (filtered by tenant for multi-tenant)
     */
    public function getLeaderboard(InternalCompetition $competition, ?int $tenantId = null): Collection
    {
        $query = InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->with(['branch', 'tenant']);

        if ($tenantId && $competition->is_multi_tenant) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->orderByDesc('score')->get();
    }

    /**
     * Get employee score statistics
     */
    public function getScoreStatistics(InternalCompetition $competition): array
    {
        $employees = InternalCompetitionEmployee::where('competition_id', $competition->id)->get();

        if ($employees->isEmpty()) {
            return [
                'count' => 0,
                'average_score' => 0,
                'min_score' => 0,
                'max_score' => 0,
                'total_mentions' => 0,
                'avg_mentions_per_employee' => 0,
            ];
        }

        $scores = $employees->pluck('score');

        return [
            'count' => $employees->count(),
            'average_score' => round($scores->avg(), 2),
            'min_score' => round($scores->min(), 2),
            'max_score' => round($scores->max(), 2),
            'total_mentions' => $employees->sum('total_mentions'),
            'avg_mentions_per_employee' => round($employees->avg('total_mentions'), 2),
            'total_positive' => $employees->sum('positive_mentions'),
            'total_negative' => $employees->sum('negative_mentions'),
            'total_neutral' => $employees->sum('neutral_mentions'),
        ];
    }

    /**
     * Get best employee per branch
     */
    public function getBestEmployeePerBranch(InternalCompetition $competition): Collection
    {
        return InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->select('branch_id', DB::raw('MAX(score) as max_score'))
            ->groupBy('branch_id')
            ->get()
            ->map(function ($item) use ($competition) {
                return InternalCompetitionEmployee::where('competition_id', $competition->id)
                    ->where('branch_id', $item->branch_id)
                    ->where('score', $item->max_score)
                    ->with(['branch', 'tenant'])
                    ->first();
            })
            ->filter();
    }

    /**
     * Merge duplicate employees (manual operation)
     */
    public function mergeEmployees(
        InternalCompetition $competition,
        int $primaryEmployeeId,
        array $duplicateEmployeeIds
    ): InternalCompetitionEmployee {
        $primary = InternalCompetitionEmployee::findOrFail($primaryEmployeeId);
        $duplicates = InternalCompetitionEmployee::whereIn('id', $duplicateEmployeeIds)->get();

        DB::beginTransaction();

        try {
            foreach ($duplicates as $duplicate) {
                // Sum up the mentions
                $primary->total_mentions += $duplicate->total_mentions;
                $primary->positive_mentions += $duplicate->positive_mentions;
                $primary->negative_mentions += $duplicate->negative_mentions;
                $primary->neutral_mentions += $duplicate->neutral_mentions;

                // Merge sample mentions
                $primary->sample_positive_mentions = array_slice(
                    array_merge(
                        $primary->sample_positive_mentions ?? [],
                        $duplicate->sample_positive_mentions ?? []
                    ),
                    0,
                    self::MAX_SAMPLE_MENTIONS
                );

                $primary->sample_negative_mentions = array_slice(
                    array_merge(
                        $primary->sample_negative_mentions ?? [],
                        $duplicate->sample_negative_mentions ?? []
                    ),
                    0,
                    self::MAX_SAMPLE_MENTIONS
                );

                // Update date range
                if ($duplicate->first_mention_at &&
                    (!$primary->first_mention_at || $duplicate->first_mention_at < $primary->first_mention_at)) {
                    $primary->first_mention_at = $duplicate->first_mention_at;
                }

                if ($duplicate->last_mention_at &&
                    (!$primary->last_mention_at || $duplicate->last_mention_at > $primary->last_mention_at)) {
                    $primary->last_mention_at = $duplicate->last_mention_at;
                }

                // Delete duplicate
                $duplicate->delete();
            }

            // Recalculate score
            $primary->score = ($primary->positive_mentions * 10)
                            + ($primary->neutral_mentions * 1)
                            - ($primary->negative_mentions * 5);
            $primary->calculated_at = now();
            $primary->save();

            // Update rankings
            $this->updateRankings($competition);

            DB::commit();

            Log::info('Employees merged', [
                'competition_id' => $competition->id,
                'primary_id' => $primaryEmployeeId,
                'merged_ids' => $duplicateEmployeeIds,
            ]);

            return $primary->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add a mention to an employee
     */
    public function addMention(
        InternalCompetitionEmployee $employee,
        string $sentiment,
        ?string $reviewText = null
    ): InternalCompetitionEmployee {
        $employee->total_mentions++;

        switch (strtolower($sentiment)) {
            case 'positive':
                $employee->positive_mentions++;
                if ($reviewText && count($employee->sample_positive_mentions ?? []) < self::MAX_SAMPLE_MENTIONS) {
                    $samples = $employee->sample_positive_mentions ?? [];
                    $samples[] = $reviewText;
                    $employee->sample_positive_mentions = $samples;
                }
                break;
            case 'negative':
                $employee->negative_mentions++;
                if ($reviewText && count($employee->sample_negative_mentions ?? []) < self::MAX_SAMPLE_MENTIONS) {
                    $samples = $employee->sample_negative_mentions ?? [];
                    $samples[] = $reviewText;
                    $employee->sample_negative_mentions = $samples;
                }
                break;
            default:
                $employee->neutral_mentions++;
        }

        // Recalculate score
        $employee->score = ($employee->positive_mentions * 10)
                         + ($employee->neutral_mentions * 1)
                         - ($employee->negative_mentions * 5);
        $employee->last_mention_at = now();
        $employee->calculated_at = now();
        $employee->save();

        return $employee;
    }

    /**
     * Find potential duplicate employees
     */
    public function findPotentialDuplicates(InternalCompetition $competition): array
    {
        $employees = InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->get();

        $duplicateGroups = [];
        $processed = [];

        foreach ($employees as $employee) {
            if (in_array($employee->id, $processed)) {
                continue;
            }

            $group = [$employee];
            $processed[] = $employee->id;

            foreach ($employees as $other) {
                if ($employee->id === $other->id || in_array($other->id, $processed)) {
                    continue;
                }

                // Same branch and similar names
                if ($employee->branch_id === $other->branch_id &&
                    $this->nameNormalizer->areSamePerson(
                        $employee->normalized_name,
                        $other->normalized_name,
                        75.0 // Slightly lower threshold for detection
                    )) {
                    $group[] = $other;
                    $processed[] = $other->id;
                }
            }

            if (count($group) > 1) {
                $duplicateGroups[] = [
                    'branch_id' => $employee->branch_id,
                    'employees' => $group,
                    'similarity' => $this->nameNormalizer->calculateSimilarity(
                        $group[0]->normalized_name,
                        $group[1]->normalized_name
                    ),
                ];
            }
        }

        return $duplicateGroups;
    }
}
