<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\AiAssistant\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'ai-assistant', 'as' => 'ai-assistant.'], function (): void {
            // Settings routes
            Route::get('settings', [
                'as' => 'settings.index',
                'uses' => 'AiSettingsController@index',
                'permission' => 'ai-assistant.settings.index',
            ]);
            Route::post('settings', [
                'as' => 'settings.update',
                'uses' => 'AiSettingsController@update',
                'permission' => 'ai-assistant.settings.update',
            ]);
            Route::post('settings/reset', [
                'as' => 'settings.reset',
                'uses' => 'AiSettingsController@reset',
                'permission' => 'ai-assistant.settings.reset',
            ]);

            // API Keys routes
            // CRITICAL: Specific routes MUST come before Route::resource to avoid conflicts
            Route::get('api-keys/get-models/{providerId}', [
                'as' => 'keys.provider-models',
                'uses' => 'AiApiKeyController@getProviderModels',
                // No permission - allow anyone in admin to fetch models
            ])->where('providerId', '[0-9]+'); // Only match numeric IDs
            
            Route::post('api-keys/reorder', [
                'as' => 'keys.reorder',
                'uses' => 'AiApiKeyController@reorder',
                'permission' => 'ai-assistant.keys.edit',
            ]);
            
            Route::post('api-keys/{apiKey}/toggle-status', [
                'as' => 'keys.toggle-status',
                'uses' => 'AiApiKeyController@toggleStatus',
                'permission' => 'ai-assistant.keys.edit',
            ]);
            
            // Resource routes LAST to avoid catching specific routes above
            Route::resource('api-keys', 'AiApiKeyController', ['names' => 'keys']);

            // Custom Instructions routes
            Route::resource('instructions', 'AiCustomInstructionController', ['names' => 'instructions']);

            // Usage & Analytics routes
            Route::get('usage', [
                'as' => 'usage.index',
                'uses' => 'AiUsageController@index',
                'permission' => 'ai-assistant.usage.index',
            ]);
            Route::get('usage/export', [
                'as' => 'usage.export',
                'uses' => 'AiUsageController@export',
                'permission' => 'ai-assistant.usage.export',
            ]);
            Route::post('usage/clear-old', [
                'as' => 'usage.clear-old',
                'uses' => 'AiUsageController@clearOldLogs',
                'permission' => 'ai-assistant.usage.clear',
            ]);

            // API endpoints (AJAX)
            Route::post('api/generate-text', [
                'as' => 'api.generate-text',
                'uses' => 'AiGenerationController@generateText',
                'permission' => 'ai-assistant.api.generate-text',
            ]);
            Route::post('api/generate-image', [
                'as' => 'api.generate-image',
                'uses' => 'AiGenerationController@generateImage',
                'permission' => 'ai-assistant.api.generate-image',
            ]);
            Route::get('api/available-models', [
                'as' => 'api.available-models',
                'uses' => 'AiGenerationController@getAvailableModels',
                'permission' => 'ai-assistant.api.models',
            ]);
            Route::get('api/custom-instructions', [
                'as' => 'api.custom-instructions',
                'uses' => 'AiCustomInstructionController@getActive',
                'permission' => 'ai-assistant.api.instructions',
            ]);
        });
    });
});


