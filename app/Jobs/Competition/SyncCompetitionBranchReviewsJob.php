<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Services\Competition\GooglePlacesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCompetitionBranchReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected CompetitionBranch $branch
    ) {}

    public function handle(GooglePlacesService $placesService): void
    {
        try {
            $this->branch->updateSyncStatus('syncing');

            // Get latest place details
            $result = $placesService->getPlaceDetails($this->branch->google_place_id);

            if (!$result['success'] || !$result['place']) {
                throw new \Exception('Failed to fetch place details');
            }

            $placeData = $result['place'];

            // Update branch with latest Google data
            $this->branch->update([
                'google_rating' => $placeData['rating'],
                'google_reviews_count' => $placeData['reviews_count'],
                'photo_url' => $placeData['photo_url'],
                'photos' => $placeData['photos'],
                'opening_hours' => $placeData['opening_hours'],
                'reviews_last_synced_at' => now(),
                'reviews_synced_count' => $placeData['reviews_count'],
                'sync_status' => 'synced',
                'sync_error' => null,
            ]);

            Log::info('Competition branch reviews synced', [
                'branch_id' => $this->branch->id,
                'rating' => $placeData['rating'],
                'reviews_count' => $placeData['reviews_count'],
            ]);

        } catch (\Exception $e) {
            $this->branch->updateSyncStatus('failed', $e->getMessage());

            Log::error('Competition branch sync failed', [
                'branch_id' => $this->branch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->branch->updateSyncStatus('failed', $exception->getMessage());
    }
}
