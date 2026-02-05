<?php

namespace Plugin\ResearchAssistant\Services\Providers;

interface AIProviderInterface
{
    /**
     * Get the provider name for display.
     */
    public function getName(): string;

    /**
     * Get available models for this provider.
     *
     * @return array<string, string> Model ID => Model name
     */
    public function getModels(): array;

    /**
     * Analyze the given prompt and context using AI.
     *
     * @param string $prompt The main prompt/question
     * @param array $context Additional context (model, temperature, etc.)
     * @return array{content: string, tokens: int, model: string}
     * @throws \Exception If the API call fails
     */
    public function analyze(string $prompt, array $context = []): array;

    /**
     * Check if the provider is configured with a valid API key.
     */
    public function isConfigured(): bool;

    /**
     * Get the number of tokens used in the last request.
     */
    public function getTokensUsed(): int;

    /**
     * Set the API key for this provider.
     */
    public function setApiKey(string $apiKey): void;
}
