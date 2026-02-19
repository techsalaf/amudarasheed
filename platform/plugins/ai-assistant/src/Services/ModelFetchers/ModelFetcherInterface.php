<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

interface ModelFetcherInterface
{
    /**
     * Fetch available models from the provider's API
     *
     * @param string $apiKey The API key for the provider
     * @return array List of available model identifiers
     * @throws \Exception If API call fails
     */
    public function fetchModels(string $apiKey): array;

    /**
     * Get the provider key (e.g., 'openai', 'anthropic')
     */
    public function getProviderKey(): string;
}
