<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Analyze sentiment of a review text.
     * 
     * @param string $text Review text to analyze
     * @return array{sentiment: string, score: float, categories: array, keywords: array}
     */
    public function analyzeSentiment(string $text): array;

    /**
     * Generate a reply for a review.
     * 
     * @param string $reviewText The original review text
     * @param int $rating The star rating (1-5)
     * @param string $tone The desired tone (professional, friendly, apologetic, etc.)
     * @param string|null $businessName Optional business name for personalization
     * @return string The generated reply text
     */
    public function generateReply(string $reviewText, int $rating, string $tone = 'professional', ?string $businessName = null): string;

    /**
     * Detect the likely gender of a reviewer from their name.
     * 
     * @param string $name Reviewer name
     * @return string 'male', 'female', or 'unknown'
     */
    public function detectGender(string $name): string;

    /**
     * Extract keywords and topics from review text.
     * 
     * @param string $text Review text
     * @return array{keywords: array, categories: array}
     */
    public function extractKeywords(string $text): array;

    /**
     * Generate business recommendations based on reviews.
     * 
     * @param array $reviews Array of review texts and sentiments
     * @return array List of actionable recommendations
     */
    public function generateRecommendations(array $reviews): array;

    /**
     * Get the provider name.
     * 
     * @return string 'openai' or 'anthropic'
     */
    public function getProviderName(): string;
}
