<?php

namespace App\Services\AI;

use InvalidArgumentException;

class AIServiceFactory
{
    /**
     * Create an AI service instance based on configuration.
     */
    public static function make(?string $provider = null): AIServiceInterface
    {
        $provider = $provider ?? config('ai.default_provider', 'openai');

        return match($provider) {
            'openai' => new OpenAIService(),
            'anthropic' => new AnthropicService(),
            default => throw new InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }

    /**
     * Get the default provider name.
     */
    public static function getDefaultProvider(): string
    {
        return config('ai.default_provider', 'openai');
    }

    /**
     * Get all available providers.
     */
    public static function getAvailableProviders(): array
    {
        return ['openai', 'anthropic'];
    }

    /**
     * Check if a provider is configured.
     */
    public static function isProviderConfigured(string $provider): bool
    {
        return match($provider) {
            'openai' => !empty(config('ai.openai.api_key')),
            'anthropic' => !empty(config('ai.anthropic.api_key')),
            default => false,
        };
    }
}
