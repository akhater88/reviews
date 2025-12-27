<?php

namespace App\Services\Reviews;

use App\Enums\BranchSource;
use App\Enums\SyncStatus;
use App\Models\Branch;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewSyncService
{
    public function __construct(
        private OutscraperReviewCollector $outscraperCollector
    ) {}

    /**
     * Sync reviews for a single branch
     */
    public function syncBranch(Branch $branch, bool $fullSync = false): array
    {
        Log::info('ReviewSync: Starting sync', [
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'source' => $branch->source?->value ?? $branch->source,
            'full_sync' => $fullSync,
        ]);

        // Update status to syncing
        $branch->update(['sync_status' => SyncStatus::SYNCING]);

        try {
            // Determine cutoff date for incremental sync
            $cutoff = null;
            if (!$fullSync && $branch->last_synced_at) {
                // Get reviews from 1 day before last sync (overlap to catch any missed)
                $cutoff = $branch->last_synced_at->subDay()->timestamp;
            } elseif (!$fullSync) {
                // First sync: get last 3 months by default
                $cutoff = now()->subMonths(3)->timestamp;
            }
            // If fullSync = true, cutoff = null, gets ALL reviews

            // Collect reviews based on branch source
            $reviews = $this->collectReviews($branch, $cutoff);

            // Store reviews with duplicate prevention
            $stats = $this->storeReviews($branch, $reviews);

            // Update branch statistics
            $this->updateBranchStatistics($branch);

            // Update branch sync status
            $branch->update([
                'sync_status' => SyncStatus::COMPLETED,
                'last_synced_at' => now(),
            ]);

            Log::info('ReviewSync: Completed', [
                'branch_id' => $branch->id,
                'stats' => $stats,
            ]);

            return $stats;

        } catch (Exception $e) {
            Log::error('ReviewSync: Failed', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);

            $branch->update(['sync_status' => SyncStatus::FAILED]);

            throw $e;
        }
    }

    /**
     * Collect reviews from appropriate source
     */
    private function collectReviews(Branch $branch, ?int $cutoff): array
    {
        // Check if branch is from Google Business
        $source = $branch->source instanceof BranchSource
            ? $branch->source
            : BranchSource::tryFrom($branch->source);

        if ($source === BranchSource::GOOGLE_BUSINESS) {
            // TODO: Implement Google Business API collection
            // For now, throw exception or skip
            throw new Exception('Google Business API sync not implemented yet. Use Outscraper for now.');
        }

        // Manual branches use Outscraper
        return $this->outscraperCollector->collect($branch, $cutoff);
    }

    /**
     * Store reviews with duplicate prevention
     */
    private function storeReviews(Branch $branch, array $reviews): array
    {
        $stats = [
            'total' => count($reviews),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($reviews as $reviewData) {
            try {
                $result = $this->upsertReview($branch, $reviewData);
                $stats[$result]++;
            } catch (Exception $e) {
                Log::warning('ReviewSync: Failed to store review', [
                    'branch_id' => $branch->id,
                    'error' => $e->getMessage(),
                    'review' => $reviewData['outscraper_review_id'] ?? 'unknown',
                ]);
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Upsert a single review
     */
    private function upsertReview(Branch $branch, array $reviewData): string
    {
        // Try to find existing review
        $query = Review::withoutGlobalScopes()
            ->where('branch_id', $branch->id);

        $existing = $query->where(function ($q) use ($reviewData) {
            if (!empty($reviewData['outscraper_review_id'])) {
                $q->where('outscraper_review_id', $reviewData['outscraper_review_id']);
            }
            if (!empty($reviewData['google_review_id'])) {
                $q->orWhere('google_review_id', $reviewData['google_review_id']);
            }
        })->first();

        if ($existing) {
            // Check if we should update (e.g., owner reply added)
            $shouldUpdate = false;
            $updates = [];

            // Update if owner reply was added
            if (!$existing->owner_reply && !empty($reviewData['owner_reply'])) {
                $updates['owner_reply'] = $reviewData['owner_reply'];
                $updates['owner_reply_date'] = $reviewData['owner_reply_date'];
                $updates['is_replied'] = true;
                $updates['needs_reply'] = false;
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $existing->update($updates);
                return 'updated';
            }

            return 'skipped';
        }

        // Create new review
        Review::create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            ...$reviewData,
        ]);

        return 'created';
    }

    /**
     * Sync all branches for a tenant
     */
    public function syncTenantBranches(int $tenantId, ?string $sourceFilter = null): array
    {
        $query = Branch::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($sourceFilter) {
            $query->where('source', $sourceFilter);
        }

        $branches = $query->get();

        $results = [];
        foreach ($branches as $branch) {
            try {
                $results[$branch->id] = [
                    'name' => $branch->name,
                    'status' => 'success',
                    'stats' => $this->syncBranch($branch),
                ];
            } catch (Exception $e) {
                $results[$branch->id] = [
                    'name' => $branch->name,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Update branch statistics after sync (total_reviews, current_rating)
     */
    private function updateBranchStatistics(Branch $branch): void
    {
        $reviews = Review::withoutGlobalScopes()
            ->where('branch_id', $branch->id);

        $totalReviews = $reviews->count();
        $avgRating = $reviews->avg('rating');

        $branch->update([
            'total_reviews' => $totalReviews,
            'current_rating' => $avgRating ? round($avgRating, 1) : null,
        ]);

        Log::info('ReviewSync: Branch statistics updated', [
            'branch_id' => $branch->id,
            'total_reviews' => $totalReviews,
            'current_rating' => $avgRating,
        ]);
    }
}
