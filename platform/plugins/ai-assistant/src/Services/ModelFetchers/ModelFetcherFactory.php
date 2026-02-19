<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

class ModelFetcherFactory
{
    protected static array $fetchers = [];

    public static function register(string $provider, ModelFetcherInterface $fetcher): void
    {
        self::$fetchers[strtolower($provider)] = $fetcher;
    }

    public static function make(string $provider): ?ModelFetcherInterface
    {
        return self::$fetchers[strtolower($provider)] ?? null;
    }

    public static function initialize(): void
    {
        self::$fetchers = [
            'openai' => new OpenAiModelFetcher(),
            'claude' => new AnthropicModelFetcher(),
            'gemini' => new GoogleGeminiModelFetcher(),
            'deepseek' => new DeepSeekModelFetcher(),
            'grok' => new GrokModelFetcher(),
            'openrouter' => new OpenRouterModelFetcher(),
        ];
    }
}
