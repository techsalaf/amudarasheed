<?php

namespace Botble\AiAssistant\Providers;

use Botble\AiAssistant\Models\AiCustomInstruction;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Models\AiSetting;
use Botble\AiAssistant\Services\AiGenerationService;
use Botble\AiAssistant\Services\AiSettingsService;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Supports\ServiceProvider;

class AiAssistantServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ai-assistant.php',
            'ai-assistant'
        );

        $this->app->singleton(AiGenerationService::class);
        $this->app->singleton(AiSettingsService::class);

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Botble\AiAssistant\Commands\RefreshAiModelsCommand::class,
                \Botble\AiAssistant\Commands\TestModelsEndpointCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Use Botble's standard loading pattern
        $this
            ->setNamespace('plugins/ai-assistant')
            ->loadAndPublishConfigurations(['ai-assistant'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->loadRoutes();

        $this->registerMenus();

        // Only seed after tables exist (activation runs migrations after provider registration)
        if (\Illuminate\Support\Facades\Schema::hasTable('ai_providers') && AiProvider::count() === 0) {
            $this->seedProviders();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('ai_custom_instructions') && AiCustomInstruction::count() === 0) {
            $this->seedCustomInstructions();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('ai_settings') && AiSetting::count() === 0) {
            $aiSettings = app(AiSettingsService::class);
            $aiSettings->resetToDefaults();
        }
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    protected function registerMenus(): void
    {
        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('ai-assistant')
                        ->priority(3000)
                        ->name('AI Assistant')
                        ->icon('ti ti-robot')
                        ->route('ai-assistant.settings.index')
                        ->permissions('ai-assistant.access')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('ai-assistant-settings')
                        ->priority(1)
                        ->parentId('ai-assistant')
                        ->name('Settings')
                        ->icon('ti ti-settings')
                        ->route('ai-assistant.settings.index')
                        ->permissions('ai-assistant.settings')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('ai-assistant-keys')
                        ->priority(2)
                        ->parentId('ai-assistant')
                        ->name('API Keys')
                        ->icon('ti ti-key')
                        ->route('ai-assistant.keys.index')
                        ->permissions('ai-assistant.keys')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('ai-assistant-instructions')
                        ->priority(3)
                        ->parentId('ai-assistant')
                        ->name('Custom Instructions')
                        ->icon('ti ti-list')
                        ->route('ai-assistant.instructions.index')
                        ->permissions('ai-assistant.instructions')
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('ai-assistant-usage')
                        ->priority(4)
                        ->parentId('ai-assistant')
                        ->name('Usage & Analytics')
                        ->icon('ti ti-chart-bar')
                        ->route('ai-assistant.usage.index')
                        ->permissions('ai-assistant.usage')
                );
        });
    }

    protected function seedProviders(): void
    {
        $providers = [
            [
                'name' => 'openai',
                'display_name' => 'OpenAI',
                'description' => 'GPT-4, GPT-3.5 models from OpenAI',
                'priority' => 0,
            ],
            [
                'name' => 'gemini',
                'display_name' => 'Google Gemini',
                'description' => 'Gemini models from Google',
                'priority' => 1,
            ],
            [
                'name' => 'claude',
                'display_name' => 'Anthropic Claude',
                'description' => 'Claude models from Anthropic',
                'priority' => 2,
            ],
            [
                'name' => 'deepseek',
                'display_name' => 'DeepSeek',
                'description' => 'DeepSeek Chat models',
                'priority' => 3,
            ],
            [
                'name' => 'openrouter',
                'display_name' => 'OpenRouter',
                'description' => 'Access multiple models via OpenRouter',
                'priority' => 4,
            ],
            [
                'name' => 'grok',
                'display_name' => 'Grok',
                'description' => 'Grok models from xAI',
                'priority' => 5,
            ],
        ];

        foreach ($providers as $provider) {
            AiProvider::create($provider);
        }
    }

    protected function seedCustomInstructions(): void
    {
        $instructions = [
            [
                'name' => 'Professional Tone',
                'instruction' => 'Write in a professional, formal tone. Use proper grammar and sophisticated vocabulary. Maintain a corporate and authoritative voice.',
                'order' => 1,
            ],
            [
                'name' => 'Friendly & Conversational',
                'instruction' => 'Write in a friendly, conversational tone. Use simple language, contractions, and a warm approach. Make it engaging and approachable.',
                'order' => 2,
            ],
            [
                'name' => 'SEO Optimized',
                'instruction' => 'Write with SEO best practices in mind. Include relevant keywords naturally, use clear headings, and create compelling meta descriptions. Focus on search engine visibility.',
                'order' => 3,
            ],
            [
                'name' => 'Creative & Inspiring',
                'instruction' => 'Write creatively and inspiringly. Use vivid language, metaphors, and storytelling techniques. Make the content engaging and memorable.',
                'order' => 4,
            ],
            [
                'name' => 'Technical & Detailed',
                'instruction' => 'Write with technical precision and detail. Include specific terminology, examples, and thorough explanations. Target an informed audience.',
                'order' => 5,
            ],
        ];

        foreach ($instructions as $instruction) {
            AiCustomInstruction::create($instruction);
        }
    }

    protected function getPathNamespace(): string
    {
        return 'ai-assistant';
    }

    protected function registerInlineButtons(): void
    {
        // Not used - inline buttons are loaded via JavaScript
    }
}
