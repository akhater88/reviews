<?php

namespace App\Jobs\Analysis;

use App\Enums\AnalysisStatus;
use App\Enums\AnalysisType;
use App\Models\AnalysisOverview;
use App\Models\AnalysisResult;
use App\Services\AI\AIServiceFactory;
use App\Services\AI\AIServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class BaseAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    protected ?AIServiceInterface $aiService = null;

    public function __construct(
        protected string $restaurantId,
        protected array $reviews,
        protected int $analysisOverviewId
    ) {
        // Use Redis connection for Horizon
        $this->onConnection(config('ai.analysis.connection', 'redis'));
        $this->onQueue(config('ai.analysis.queue', 'analysis'));
    }

    /**
     * Get the tags that should be assigned to the job.
     * Used by Horizon for filtering and monitoring.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'analysis',
            'restaurant:' . $this->restaurantId,
            'overview:' . $this->analysisOverviewId,
            class_basename($this),
        ];
    }

    /**
     * Get AI service instance (OpenAI or Anthropic based on config)
     */
    protected function getAIService(): AIServiceInterface
    {
        if (!$this->aiService) {
            $this->aiService = AIServiceFactory::make();
        }
        return $this->aiService;
    }

    /**
     * Call AI with prompt and return parsed response
     */
    protected function callAI(string $prompt, array $options = []): array
    {
        $defaultOptions = [
            'timeout' => config('ai.analysis.timeout', 180),
            'max_tokens' => config('ai.analysis.max_tokens', 4000),
            'temperature' => config('ai.analysis.temperature', 0.3),
            'system_message' => 'أنت محلل متخصص في تحليل مراجعات المطاعم. أجب بـ JSON صالح فقط، بدون أي نص إضافي.',
        ];

        $options = array_merge($defaultOptions, $options);

        $startTime = now();

        Log::info("Analysis AI Call", [
            'job' => class_basename($this),
            'provider' => config('ai.default_provider'),
            'restaurant_id' => $this->restaurantId,
        ]);

        $result = $this->getAIService()->complete($prompt, $options);

        $processingTime = (int) now()->diffInSeconds($startTime);

        return [
            'result' => $result['content'],
            'processing_time' => $processingTime,
            'tokens_used' => (int) ($result['usage']['total_tokens'] ?? 0),
            'model' => $result['model'],
            'provider' => $result['provider'],
        ];
    }

    /**
     * Save analysis result to database
     */
    protected function saveAnalysis(array $data, AnalysisType $analysisType): void
    {
        AnalysisResult::updateOrCreate([
            'analysis_overview_id' => $this->analysisOverviewId,
            'analysis_type' => $analysisType->value,
        ], [
            'restaurant_id' => $this->restaurantId,
            'result' => $data['result'],
            'status' => AnalysisStatus::COMPLETED,
            'provider' => $data['provider'],
            'model' => $data['model'],
            'processing_time' => $data['processing_time'],
            'tokens_used' => $data['tokens_used'],
            'confidence' => 0.85,
            'review_count' => count($this->reviews),
            'period_start' => now()->subMonths(3)->toDateString(),
            'period_end' => now()->toDateString(),
        ]);

        // Update total tokens in overview
        $this->getAnalysisOverview()->addTokens($data['tokens_used']);
    }

    /**
     * Get analysis overview model
     */
    protected function getAnalysisOverview(): AnalysisOverview
    {
        return AnalysisOverview::findOrFail($this->analysisOverviewId);
    }

    /**
     * Update progress
     */
    protected function updateProgress(int $progress, string $step): void
    {
        $this->getAnalysisOverview()->updateProgress($progress, $step);
    }

    /**
     * Mark analysis as failed
     */
    protected function markFailed(string $error): void
    {
        Log::error("Analysis Failed", [
            'job' => class_basename($this),
            'restaurant_id' => $this->restaurantId,
            'error' => $error,
        ]);

        $this->getAnalysisOverview()->markAsFailed($error);
    }

    /**
     * Get result from a previous step
     */
    protected function getPreviousResult(AnalysisType $type): array
    {
        $result = AnalysisResult::where('analysis_overview_id', $this->analysisOverviewId)
            ->where('analysis_type', $type->value)
            ->first();

        if (!$result || empty($result->result)) {
            throw new \Exception("Previous analysis result not found: {$type->value}");
        }

        return $result->result;
    }

    /**
     * Format reviews for prompt
     */
    protected function formatReviewsForPrompt(): string
    {
        return collect($this->reviews)->map(function ($review, $index) {
            $text = $review['text'] ?? '';
            $rating = $review['rating'] ?? 5;
            $author = $review['author_name'] ?? 'عميل';
            $date = $review['review_date'] ?? '';

            if (empty($text)) {
                return "مراجعة " . ($index + 1) . ": تقييم {$rating}/5 نجوم (بدون نص) - {$author}";
            }

            return "مراجعة " . ($index + 1) . ": \"{$text}\" (تقييم: {$rating}/5) - {$author} - {$date}";
        })->implode("\n\n");
    }

    /**
     * Get reviews with text only
     */
    protected function getReviewsWithText(): array
    {
        return collect($this->reviews)
            ->filter(fn ($r) => !empty($r['text']))
            ->values()
            ->toArray();
    }

    /**
     * Get star-only reviews
     */
    protected function getStarOnlyReviews(): array
    {
        return collect($this->reviews)
            ->filter(fn ($r) => empty($r['text']))
            ->values()
            ->toArray();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Analysis Job Failed Permanently", [
            'job' => class_basename($this),
            'restaurant_id' => $this->restaurantId,
            'analysis_overview_id' => $this->analysisOverviewId,
            'error' => $exception->getMessage(),
        ]);

        $this->markFailed($exception->getMessage());
    }
}
