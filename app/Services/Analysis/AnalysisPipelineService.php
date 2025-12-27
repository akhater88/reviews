<?php

namespace App\Services\Analysis;

use App\Enums\AnalysisStatus;
use App\Jobs\Analysis\FetchRestaurantInfoJob;
use App\Models\AnalysisOverview;
use App\Models\Branch;
use App\Models\Review;
use Illuminate\Support\Facades\Log;

class AnalysisPipelineService
{
    /**
     * Start analysis pipeline for a branch
     */
    public function startAnalysis(Branch $branch, ?array $options = []): AnalysisOverview
    {
        // Get reviews for analysis
        $reviews = $this->getReviewsForAnalysis($branch, $options);

        if (empty($reviews)) {
            throw new \Exception('No reviews found for analysis');
        }

        // Create analysis overview
        $overview = AnalysisOverview::create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'restaurant_id' => $branch->google_place_id,
            'status' => AnalysisStatus::PROCESSING,
            'progress' => 0,
            'current_step' => 'initializing',
            'period_start' => $options['period_start'] ?? now()->subMonths(3),
            'period_end' => $options['period_end'] ?? now(),
            'started_at' => now(),
        ]);

        Log::info("Starting Analysis Pipeline", [
            'branch_id' => $branch->id,
            'analysis_overview_id' => $overview->id,
            'reviews_count' => count($reviews),
        ]);

        // Dispatch first job
        FetchRestaurantInfoJob::dispatch(
            $branch->google_place_id,
            $overview->id,
            $reviews
        );

        return $overview;
    }

    /**
     * Get reviews for analysis
     */
    private function getReviewsForAnalysis(Branch $branch, array $options): array
    {
        $query = Review::where('branch_id', $branch->id);

        // Apply date filters
        if (!empty($options['period_start'])) {
            $query->where('review_date', '>=', $options['period_start']);
        } else {
            $query->where('review_date', '>=', now()->subMonths(3));
        }

        if (!empty($options['period_end'])) {
            $query->where('review_date', '<=', $options['period_end']);
        }

        return $query->get()->map(function ($review) {
            return [
                'id' => $review->id,
                'author_name' => $review->reviewer_name,
                'rating' => $review->rating,
                'text' => $review->text,
                'review_date' => $review->review_date?->format('Y-m-d'),
                'language' => $review->language,
            ];
        })->toArray();
    }

    /**
     * Get analysis status
     */
    public function getStatus(int $analysisOverviewId): array
    {
        $overview = AnalysisOverview::with('results')->findOrFail($analysisOverviewId);

        return [
            'id' => $overview->id,
            'status' => $overview->status->value,
            'progress' => $overview->progress,
            'current_step' => $overview->current_step,
            'started_at' => $overview->started_at,
            'completed_at' => $overview->completed_at,
            'error_message' => $overview->error_message,
            'total_tokens' => $overview->total_tokens_used,
            'results_count' => $overview->results->count(),
        ];
    }

    /**
     * Get analysis results
     */
    public function getResults(int $analysisOverviewId): array
    {
        $overview = AnalysisOverview::with('results')->findOrFail($analysisOverviewId);

        return $overview->results->mapWithKeys(function ($result) {
            return [$result->analysis_type->value => $result->result];
        })->toArray();
    }

    /**
     * Check if branch has an active analysis
     */
    public function hasActiveAnalysis(Branch $branch): bool
    {
        return AnalysisOverview::where('branch_id', $branch->id)
            ->whereIn('status', [AnalysisStatus::PENDING, AnalysisStatus::PROCESSING])
            ->exists();
    }

    /**
     * Get latest completed analysis for branch
     */
    public function getLatestAnalysis(Branch $branch): ?AnalysisOverview
    {
        return AnalysisOverview::where('branch_id', $branch->id)
            ->where('status', AnalysisStatus::COMPLETED)
            ->latest()
            ->first();
    }

    /**
     * Cancel an in-progress analysis
     */
    public function cancelAnalysis(int $analysisOverviewId): void
    {
        $overview = AnalysisOverview::findOrFail($analysisOverviewId);

        if ($overview->isProcessing()) {
            $overview->markAsFailed('Analysis cancelled by user');

            Log::info("Analysis Pipeline Cancelled", [
                'analysis_overview_id' => $analysisOverviewId,
            ]);
        }
    }
}
