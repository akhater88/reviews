<?php

namespace App\Services;

use App\Models\FreeReport;
use App\Models\FreeReportReview;
use App\Models\FreeReportResult;
use App\Jobs\FetchFreeReportReviewsJob;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreeReportService
{
    protected OutscraperService $outscraperService;
    protected WhatsAppService $whatsAppService;

    public function __construct(
        OutscraperService $outscraperService,
        WhatsAppService $whatsAppService
    ) {
        $this->outscraperService = $outscraperService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Create a new free report request.
     */
    public function createReport(
        string $phone,
        string $placeId,
        string $businessName,
        ?string $businessAddress = null
    ): FreeReport {
        Log::info('FreeReportService: Creating report', [
            'phone' => $phone,
            'place_id' => $placeId,
            'business_name' => $businessName,
        ]);

        // Check for existing report for same phone + place
        $existingReport = FreeReport::where('phone', $this->normalizePhone($phone))
            ->where('place_id', $placeId)
            ->where('status', FreeReport::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($existingReport) {
            Log::info('FreeReportService: Returning existing report', [
                'report_id' => $existingReport->id,
            ]);

            // Regenerate magic link for existing report
            $existingReport->generateMagicLinkToken();
            return $existingReport;
        }

        // Create new report
        $report = FreeReport::create([
            'phone' => $this->normalizePhone($phone),
            'place_id' => $placeId,
            'business_name' => $businessName,
            'business_address' => $businessAddress,
            'status' => FreeReport::STATUS_PENDING,
        ]);

        // Generate magic link token
        $report->generateMagicLinkToken();

        // Dispatch the job to start the pipeline
        FetchFreeReportReviewsJob::dispatch($report);

        return $report;
    }

    /**
     * Store fetched reviews for a report.
     */
    public function storeReviews(FreeReport $report, array $reviews): int
    {
        $count = 0;

        DB::transaction(function () use ($report, $reviews, &$count) {
            foreach ($reviews as $review) {
                FreeReportReview::updateOrCreate(
                    [
                        'free_report_id' => $report->id,
                        'review_id' => $review['review_id'] ?? $review['id'] ?? uniqid('review_'),
                    ],
                    [
                        'author_name' => $review['author_title'] ?? $review['author_name'] ?? null,
                        'author_image' => $review['author_image'] ?? null,
                        'rating' => (int) ($review['review_rating'] ?? $review['rating'] ?? 0),
                        'text' => $review['review_text'] ?? $review['text'] ?? null,
                        'review_time' => $this->parseReviewTime($review['review_datetime_utc'] ?? $review['time'] ?? null),
                        'language' => $review['review_language'] ?? $review['language'] ?? null,
                        'raw_data' => $review,
                    ]
                );
                $count++;
            }
        });

        Log::info('FreeReportService: Stored reviews', [
            'report_id' => $report->id,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Generate analysis results for a report.
     */
    public function generateResults(FreeReport $report, array $analysisData): FreeReportResult
    {
        Log::info('FreeReportService: Generating results', [
            'report_id' => $report->id,
        ]);

        $result = FreeReportResult::updateOrCreate(
            ['free_report_id' => $report->id],
            [
                'overall_score' => $analysisData['overall_score'] ?? null,
                'total_reviews' => $analysisData['total_reviews'] ?? $report->reviews()->count(),
                'average_rating' => $analysisData['average_rating'] ?? $this->calculateAverageRating($report),
                'sentiment_breakdown' => $analysisData['sentiment_breakdown'] ?? null,
                'category_scores' => $analysisData['category_scores'] ?? null,
                'top_strengths' => $analysisData['top_strengths'] ?? null,
                'top_weaknesses' => $analysisData['top_weaknesses'] ?? null,
                'keyword_analysis' => $analysisData['keyword_analysis'] ?? null,
                'executive_summary' => $analysisData['executive_summary'] ?? null,
                'recommendations' => $analysisData['recommendations'] ?? null,
            ]
        );

        return $result;
    }

    /**
     * Send magic link to user via WhatsApp.
     */
    public function sendMagicLink(FreeReport $report): bool
    {
        return $this->whatsAppService->sendMagicLink($report);
    }

    /**
     * Get report by magic token.
     */
    public function getReportByToken(string $token): ?FreeReport
    {
        return FreeReport::findByMagicToken($token);
    }

    /**
     * Get report with full results.
     */
    public function getReportWithResults(FreeReport $report): array
    {
        $report->load(['reviews', 'result']);

        return [
            'id' => $report->id,
            'business_name' => $report->business_name,
            'business_address' => $report->business_address,
            'status' => $report->status,
            'created_at' => $report->created_at,
            'is_completed' => $report->isCompleted(),
            'result' => $report->result ? [
                'overall_score' => $report->result->overall_score,
                'grade' => $report->result->getGrade(),
                'grade_color' => $report->result->getGradeColor(),
                'total_reviews' => $report->result->total_reviews,
                'average_rating' => $report->result->average_rating,
                'sentiment_breakdown' => $report->result->sentiment_breakdown,
                'sentiment_percentages' => $report->result->getSentimentPercentages(),
                'category_scores' => $report->result->category_scores,
                'top_strengths' => $report->result->top_strengths,
                'top_weaknesses' => $report->result->top_weaknesses,
                'keyword_analysis' => $report->result->keyword_analysis,
                'executive_summary' => $report->result->executive_summary,
                'recommendations' => $report->result->recommendations,
            ] : null,
            'reviews_sample' => $report->reviews->take(5)->map(fn($r) => [
                'author_name' => $r->author_name,
                'rating' => $r->rating,
                'text' => $r->text,
                'review_time' => $r->review_time,
            ])->toArray(),
        ];
    }

    /**
     * Calculate average rating from stored reviews.
     */
    protected function calculateAverageRating(FreeReport $report): ?float
    {
        $avg = $report->reviews()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Parse review time from various formats.
     */
    protected function parseReviewTime(?string $time): ?\DateTime
    {
        if (!$time) {
            return null;
        }

        try {
            return new \DateTime($time);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Normalize phone number.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
