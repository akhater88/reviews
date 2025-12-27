<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStep;
use App\Models\AnalysisOverview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchRestaurantInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        protected string $restaurantId,
        protected int $analysisOverviewId,
        protected array $reviews
    ) {
        // Use Redis connection for Horizon
        $this->onConnection(config('ai.analysis.connection', 'redis'));
        $this->onQueue(config('ai.analysis.queue', 'analysis'));
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'analysis',
            'restaurant:' . $this->restaurantId,
            'overview:' . $this->analysisOverviewId,
            'FetchRestaurantInfoJob',
        ];
    }

    public function handle(): void
    {
        try {
            $overview = AnalysisOverview::findOrFail($this->analysisOverviewId);

            // Update review counts
            $reviewsWithText = collect($this->reviews)->filter(fn ($r) => !empty($r['text']))->count();
            $starOnlyReviews = count($this->reviews) - $reviewsWithText;

            $overview->update([
                'total_reviews' => count($this->reviews),
                'reviews_with_text' => $reviewsWithText,
                'star_only_reviews' => $starOnlyReviews,
                'progress' => 10,
                'current_step' => AnalysisStep::FETCH_RESTAURANT_INFO->value,
            ]);

            Log::info("FetchRestaurantInfoJob completed", [
                'restaurant_id' => $this->restaurantId,
                'total_reviews' => count($this->reviews),
            ]);

            // Dispatch next job
            AnalyzeSentimentJob::dispatch(
                $this->restaurantId,
                $this->reviews,
                $this->analysisOverviewId
            );

        } catch (\Exception $e) {
            Log::error("FetchRestaurantInfoJob failed", [
                'restaurant_id' => $this->restaurantId,
                'error' => $e->getMessage(),
            ]);

            AnalysisOverview::where('id', $this->analysisOverviewId)
                ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("FetchRestaurantInfoJob Failed Permanently", [
            'restaurant_id' => $this->restaurantId,
            'error' => $exception->getMessage(),
        ]);

        AnalysisOverview::where('id', $this->analysisOverviewId)
            ->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
    }
}
