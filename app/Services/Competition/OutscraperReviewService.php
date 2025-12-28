<?php

namespace App\Services\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionReview;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutscraperReviewService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.app.outscraper.com';
    protected array $config;

    public function __construct()
    {
        $this->config = config('competition.outscraper');
        $this->apiKey = $this->config['api_key'] ?? '';
    }

    /**
     * Fetch reviews for a branch from Outscraper
     */
    public function fetchReviews(CompetitionBranch $branch, int $limit = 100): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Outscraper API key not configured');
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])->timeout(120)->get("{$this->baseUrl}/maps/reviews-v3", [
                'query' => $branch->google_place_id,
                'reviewsLimit' => $limit,
                'language' => $this->config['language'] ?? 'ar',
                'sort' => 'newest',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Outscraper API error: ' . $response->status());
            }

            $data = $response->json();

            return $this->processReviewsResponse($branch, $data);

        } catch (\Exception $e) {
            Log::error('Outscraper review fetch failed', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process reviews response and save to database
     */
    protected function processReviewsResponse(CompetitionBranch $branch, array $data): array
    {
        $results = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $places = $data['data'] ?? $data;

        if (empty($places) || !is_array($places)) {
            return $results;
        }

        $place = $places[0] ?? [];
        $reviews = $place['reviews_data'] ?? [];

        foreach ($reviews as $reviewData) {
            $results['fetched']++;

            try {
                $result = $this->saveReview($branch, $reviewData);

                if ($result === 'created') {
                    $results['created']++;
                } elseif ($result === 'updated') {
                    $results['updated']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to save review', [
                    'branch_id' => $branch->id,
                    'error' => $e->getMessage(),
                ]);
                $results['skipped']++;
            }
        }

        // Update branch sync status
        $branch->update([
            'reviews_last_synced_at' => now(),
            'reviews_synced_count' => $results['fetched'],
            'sync_status' => 'synced',
        ]);

        return $results;
    }

    /**
     * Save a single review
     */
    protected function saveReview(CompetitionBranch $branch, array $data): string
    {
        $googleReviewId = $data['review_id'] ?? $data['id'] ?? null;

        if (!$googleReviewId) {
            return 'skipped';
        }

        $existing = CompetitionReview::where('google_review_id', $googleReviewId)->first();

        $reviewData = [
            'competition_branch_id' => $branch->id,
            'google_review_id' => $googleReviewId,
            'reviewer_name' => $data['author_title'] ?? $data['reviewer_name'] ?? 'Anonymous',
            'reviewer_photo_url' => $data['author_image'] ?? null,
            'rating' => (int) ($data['review_rating'] ?? $data['rating'] ?? 0),
            'review_text' => $data['review_text'] ?? $data['text'] ?? '',
            'review_date' => $this->parseReviewDate($data),
            'review_likes' => (int) ($data['review_likes'] ?? 0),
            'has_owner_response' => !empty($data['owner_answer']),
            'owner_response' => $data['owner_answer'] ?? null,
            'owner_response_date' => $this->parseOwnerResponseDate($data),
            'review_language' => $data['review_language'] ?? 'ar',
            'review_photos' => $data['review_photos'] ?? [],
        ];

        if ($existing) {
            $existing->update($reviewData);

            return 'updated';
        }

        CompetitionReview::create($reviewData);

        return 'created';
    }

    /**
     * Parse review date from various formats
     */
    protected function parseReviewDate(array $data): ?\DateTime
    {
        $dateString = $data['review_datetime_utc'] ?? $data['review_date'] ?? $data['date'] ?? null;

        if (!$dateString) {
            return now();
        }

        try {
            return new \DateTime($dateString);
        } catch (\Exception $e) {
            return now();
        }
    }

    /**
     * Parse owner response date
     */
    protected function parseOwnerResponseDate(array $data): ?\DateTime
    {
        $dateString = $data['owner_answer_timestamp_datetime_utc'] ?? null;

        if (!$dateString) {
            return null;
        }

        try {
            return new \DateTime($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }
}
