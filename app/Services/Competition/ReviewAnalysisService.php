<?php

namespace App\Services\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionReview;
use App\Services\AI\AIServiceFactory;
use App\Services\AI\AIServiceInterface;
use Illuminate\Support\Facades\Log;

class ReviewAnalysisService
{
    protected AIServiceInterface $aiProvider;
    protected array $config;

    public function __construct()
    {
        $this->config = config('competition.analysis');
        $providerName = $this->config['provider'] ?? 'anthropic';
        $this->aiProvider = AIServiceFactory::make($providerName);
    }

    /**
     * Analyze reviews for a branch
     */
    public function analyzeReviews(CompetitionBranch $branch, int $limit = 50): int
    {
        $reviews = CompetitionReview::where('competition_branch_id', $branch->id)
            ->whereNull('sentiment_score')
            ->orderByDesc('review_date')
            ->limit($limit)
            ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        $analyzed = 0;

        // Process in batches
        $batches = $reviews->chunk(10);

        foreach ($batches as $batch) {
            try {
                $this->analyzeBatch($batch);
                $analyzed += $batch->count();

                // Delay between batches
                if (($this->config['batch_delay_seconds'] ?? 0) > 0) {
                    sleep($this->config['batch_delay_seconds']);
                }
            } catch (\Exception $e) {
                Log::error('Review batch analysis failed', [
                    'branch_id' => $branch->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $analyzed;
    }

    /**
     * Analyze a batch of reviews
     */
    protected function analyzeBatch($reviews): void
    {
        $reviewTexts = $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'text' => $review->review_text,
                'rating' => $review->rating,
            ];
        })->toArray();

        $prompt = $this->buildAnalysisPrompt($reviewTexts);

        $response = $this->aiProvider->complete($prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.3,
        ]);

        $results = $this->parseAnalysisResponse($response['content'] ?? '');

        foreach ($results as $result) {
            $review = $reviews->firstWhere('id', $result['id']);
            if ($review) {
                $review->update([
                    'sentiment_score' => $result['sentiment_score'] ?? null,
                    'sentiment_label' => $result['sentiment_label'] ?? null,
                    'keywords' => $result['keywords'] ?? [],
                    'categories' => $result['categories'] ?? [],
                    'analyzed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Build analysis prompt
     */
    protected function buildAnalysisPrompt(array $reviews): string
    {
        $reviewsJson = json_encode($reviews, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Analyze the following restaurant reviews and provide sentiment analysis for each.

Reviews:
{$reviewsJson}

For each review, provide:
1. sentiment_score: 0-100 (0=very negative, 50=neutral, 100=very positive)
2. sentiment_label: "positive", "negative", or "neutral"
3. keywords: Array of key terms mentioned (food quality, service, cleanliness, etc.)
4. categories: Array of categories mentioned (food, service, ambiance, price, location)

Respond in JSON format only, no additional text:
{
  "results": [
    {
      "id": <review_id>,
      "sentiment_score": <0-100>,
      "sentiment_label": "<positive|negative|neutral>",
      "keywords": ["keyword1", "keyword2"],
      "categories": ["food", "service"]
    }
  ]
}

Important:
- Analyze both Arabic and English text
- Consider the star rating as context
- Extract meaningful keywords in the original language
- Be accurate with sentiment scoring
PROMPT;
    }

    /**
     * Parse AI response
     */
    protected function parseAnalysisResponse(string $response): array
    {
        try {
            // Extract JSON from response
            preg_match('/\{[\s\S]*\}/', $response, $matches);

            if (empty($matches)) {
                throw new \Exception('No JSON found in response');
            }

            $data = json_decode($matches[0], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON: ' . json_last_error_msg());
            }

            return $data['results'] ?? [];

        } catch (\Exception $e) {
            Log::error('Failed to parse AI response', [
                'error' => $e->getMessage(),
                'response' => substr($response, 0, 500),
            ]);

            return [];
        }
    }

    /**
     * Analyze a single review
     */
    public function analyzeSingleReview(CompetitionReview $review): bool
    {
        try {
            $prompt = $this->buildSingleReviewPrompt($review);
            $response = $this->aiProvider->complete($prompt, [
                'max_tokens' => 1000,
                'temperature' => 0.3,
            ]);
            $result = $this->parseSingleReviewResponse($response['content'] ?? '');

            if ($result) {
                $review->update([
                    'sentiment_score' => $result['sentiment_score'] ?? null,
                    'sentiment_label' => $result['sentiment_label'] ?? null,
                    'keywords' => $result['keywords'] ?? [],
                    'categories' => $result['categories'] ?? [],
                    'analyzed_at' => now(),
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Single review analysis failed', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build prompt for single review
     */
    protected function buildSingleReviewPrompt(CompetitionReview $review): string
    {
        return <<<PROMPT
Analyze this restaurant review:

Rating: {$review->rating}/5
Text: {$review->review_text}

Provide:
1. sentiment_score: 0-100
2. sentiment_label: positive/negative/neutral
3. keywords: key terms array
4. categories: mentioned categories

Respond in JSON only.
PROMPT;
    }

    /**
     * Parse single review response
     */
    protected function parseSingleReviewResponse(string $response): ?array
    {
        try {
            preg_match('/\{[\s\S]*\}/', $response, $matches);
            if (empty($matches)) {
                return null;
            }

            return json_decode($matches[0], true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
