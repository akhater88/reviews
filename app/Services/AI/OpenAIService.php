<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService implements AIServiceInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->model = config('ai.openai.model', 'gpt-4o-mini');
    }

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function analyzeSentiment(string $text): array
    {
        $prompt = <<<PROMPT
Analyze the following customer review and provide a JSON response with:
1. sentiment: "positive", "neutral", or "negative"
2. score: a float from -1.0 (very negative) to 1.0 (very positive)
3. categories: array of relevant categories from ["food", "service", "price", "ambiance", "cleanliness", "speed", "staff", "quality"]
4. keywords: array of key phrases or words mentioned (max 10)

Review text:
"{$text}"

Respond ONLY with valid JSON, no additional text.
PROMPT;

        try {
            $response = $this->chat($prompt);
            $data = json_decode($response, true);
            
            return [
                'sentiment' => $data['sentiment'] ?? 'neutral',
                'score' => (float) ($data['score'] ?? 0),
                'categories' => $data['categories'] ?? [],
                'keywords' => $data['keywords'] ?? [],
            ];
        } catch (Exception $e) {
            Log::error('OpenAI sentiment analysis failed', ['error' => $e->getMessage()]);
            return [
                'sentiment' => 'neutral',
                'score' => 0,
                'categories' => [],
                'keywords' => [],
            ];
        }
    }

    public function generateReply(string $reviewText, int $rating, string $tone = 'professional', ?string $businessName = null): string
    {
        $businessContext = $businessName ? "You are responding on behalf of {$businessName}." : "You are responding on behalf of a restaurant.";
        
        $toneInstructions = match($tone) {
            'professional' => 'Use a professional, courteous tone. Be formal but warm.',
            'friendly' => 'Use a warm, friendly, conversational tone. Be personable and genuine.',
            'apologetic' => 'Express sincere apology and commitment to improvement. Be empathetic and understanding.',
            'grateful' => 'Express heartfelt gratitude and appreciation. Be enthusiastic and thankful.',
            'neutral' => 'Use a balanced, neutral tone. Be respectful and straightforward.',
            default => 'Use a professional, courteous tone.',
        };

        $prompt = <<<PROMPT
{$businessContext}

Generate a reply to this customer review in Arabic. The reply should:
1. Be in Arabic language
2. {$toneInstructions}
3. Be concise (2-4 sentences)
4. Address specific points mentioned in the review if applicable
5. Thank the customer appropriately based on the rating ({$rating} stars)
6. If rating is low (1-2 stars), acknowledge their concerns
7. If rating is high (4-5 stars), express gratitude
8. Do NOT include any English text

Customer Review ({$rating} stars):
"{$reviewText}"

Reply in Arabic only:
PROMPT;

        try {
            return trim($this->chat($prompt));
        } catch (Exception $e) {
            Log::error('OpenAI reply generation failed', ['error' => $e->getMessage()]);
            throw new Exception('فشل في إنشاء الرد. يرجى المحاولة مرة أخرى.');
        }
    }

    public function detectGender(string $name): string
    {
        $prompt = <<<PROMPT
Based on this name, determine if it's likely male, female, or unknown/ambiguous.
Consider Arabic, English, and other common naming conventions.
Respond with ONLY one word: "male", "female", or "unknown"

Name: "{$name}"
PROMPT;

        try {
            $response = strtolower(trim($this->chat($prompt)));
            return in_array($response, ['male', 'female']) ? $response : 'unknown';
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    public function extractKeywords(string $text): array
    {
        $prompt = <<<PROMPT
Extract keywords and categorize this review. Respond with JSON only:
{
    "keywords": ["keyword1", "keyword2", ...],
    "categories": ["food", "service", etc.]
}

Categories to choose from: food, service, price, ambiance, cleanliness, speed, staff, quality

Review: "{$text}"
PROMPT;

        try {
            $response = $this->chat($prompt);
            $data = json_decode($response, true);
            
            return [
                'keywords' => $data['keywords'] ?? [],
                'categories' => $data['categories'] ?? [],
            ];
        } catch (Exception $e) {
            return ['keywords' => [], 'categories' => []];
        }
    }

    public function generateRecommendations(array $reviews): array
    {
        $reviewsText = collect($reviews)->map(function ($review) {
            return "- Rating: {$review['rating']}, Sentiment: {$review['sentiment']}, Text: {$review['text']}";
        })->implode("\n");

        $prompt = <<<PROMPT
Based on these customer reviews, provide 3-5 actionable business recommendations in Arabic.
Focus on areas that need improvement and ways to maintain positive aspects.

Reviews:
{$reviewsText}

Respond with a JSON array of recommendations in Arabic:
["recommendation 1", "recommendation 2", ...]
PROMPT;

        try {
            $response = $this->chat($prompt);
            return json_decode($response, true) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function chat(string $prompt): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            throw new Exception('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content', '');
    }

    /**
     * Complete a prompt and return structured response.
     * Used for analysis pipeline jobs.
     */
    public function complete(string $prompt, array $options = []): array
    {
        $timeout = (int) ($options['timeout'] ?? config('ai.analysis.timeout', 180));
        $maxTokens = (int) ($options['max_tokens'] ?? config('ai.analysis.max_tokens', 4000));
        $temperature = (float) ($options['temperature'] ?? config('ai.analysis.temperature', 0.3));
        $systemMessage = $options['system_message'] ?? 'أنت محلل متخصص في تحليل مراجعات المطاعم. أجب بـ JSON صالح فقط، بدون أي نص إضافي.';

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

        if (!$response->successful()) {
            throw new Exception('OpenAI API error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content', '');
        $content = $this->cleanJsonResponse($content);

        return [
            'content' => json_decode($content, true) ?? [],
            'usage' => $response->json('usage', []),
            'model' => $this->model,
            'provider' => 'openai',
        ];
    }

    /**
     * Clean JSON response from markdown formatting.
     */
    private function cleanJsonResponse(string $content): string
    {
        $content = preg_replace('/```json\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        return trim($content);
    }
}
