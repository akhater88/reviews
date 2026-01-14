<?php

namespace App\Jobs;

use App\Models\FreeReport;
use App\Services\OutscraperService;
use App\Services\FreeReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchFreeReportReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes
    public int $backoff = 60;  // 1 minute between retries

    public function __construct(
        public FreeReport $report
    ) {}

    public function handle(
        OutscraperService $outscraperService,
        FreeReportService $freeReportService
    ): void {
        Log::info('FetchFreeReportReviewsJob: Starting', [
            'report_id' => $this->report->id,
            'place_id' => $this->report->place_id,
        ]);

        // Update status
        $this->report->updateStatus(FreeReport::STATUS_FETCHING_REVIEWS);

        try {
            // Fetch reviews from Outscraper
            $result = $outscraperService->fetchReviews(
                placeId: $this->report->place_id,
                limit: 100 // Get up to 100 reviews for analysis
            );

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to fetch reviews');
            }

            $reviews = $result['reviews'];

            if (empty($reviews)) {
                Log::warning('FetchFreeReportReviewsJob: No reviews found', [
                    'report_id' => $this->report->id,
                ]);

                // Still continue with empty reviews
                $this->report->updateStatus(FreeReport::STATUS_ANALYZING);
                AnalyzeFreeReportJob::dispatch($this->report);
                return;
            }

            // Store reviews
            $count = $freeReportService->storeReviews($this->report, $reviews);

            Log::info('FetchFreeReportReviewsJob: Completed', [
                'report_id' => $this->report->id,
                'reviews_count' => $count,
            ]);

            // Dispatch next job in pipeline
            $this->report->updateStatus(FreeReport::STATUS_ANALYZING);
            AnalyzeFreeReportJob::dispatch($this->report);

        } catch (\Exception $e) {
            Log::error('FetchFreeReportReviewsJob: Failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('FetchFreeReportReviewsJob: Permanently failed', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);

        $this->report->updateStatus(
            FreeReport::STATUS_FAILED,
            'فشل في جلب التقييمات: ' . $exception->getMessage()
        );
    }
}
