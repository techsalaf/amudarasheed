# AI Provider Models - Dynamic Fetching System

## Overview
The AI Assistant plugin now dynamically fetches available models from each AI provider's official API instead of using hardcoded defaults. This ensures you always have access to the latest models as providers release them.

## How It Works

### 1. **Model Fetching Architecture**
The system uses a provider-based architecture with specialized fetchers for each AI service:

- **OpenAI** - Fetches from `https://api.openai.com/v1/models`
- **Anthropic (Claude)** - Fetches from `https://api.anthropic.com/v1/models`
- **Google Gemini** - Fetches from `https://generativelanguage.googleapis.com/v1beta/models`
- **DeepSeek** - Fetches from `https://api.deepseek.com/v1/models`
- **Grok (xAI)** - Fetches from `https://api.x.ai/v1/models`
- **OpenRouter** - Fetches from `https://openrouter.ai/api/v1/models`

### 2. **Model Fetching Flow**
```
User selects provider in API Key form
        ↓
Frontend sends AJAX request to /admincp/ai-assistant/api-keys/get-models/{providerId}
        ↓
Controller calls fetchModelsFromProvider()
        ↓
Service checks model cache (1-hour duration)
        ↓
If cached: Return cached models
If not cached or expired:
  - Get active API key for provider
  - Decrypt API key
  - Call provider's API via ModelFetcher
  - Cache results for 1 hour
  - Return models list
        ↓
Models appear in dropdown on frontend
```

### 3. **Caching System**
- **Duration**: 1 hour per provider
- **Key Pattern**: `ai_models_{provider_id}`
- **Benefits**: 
  - Reduces API calls to providers
  - Faster response times
  - Falls back gracefully if API is unavailable

### 4. **Fallback Behavior**
If an API call fails or no models are returned:
1. The system returns cached models (if available)
2. Falls back to previously used models from database
3. Returns empty list if no fallback available

## Files Structure

### Model Fetchers
```
src/Services/ModelFetchers/
├── ModelFetcherInterface.php        # Base interface for all fetchers
├── ModelFetcherFactory.php          # Factory to manage fetchers
├── OpenAiModelFetcher.php
├── AnthropicModelFetcher.php
├── GoogleGeminiModelFetcher.php
├── DeepSeekModelFetcher.php
├── GrokModelFetcher.php
└── OpenRouterModelFetcher.php
```

### Services
```
src/Services/
├── ModelCacheService.php            # Handles model caching
└── ...other services...
```

### Commands
```
src/Commands/
└── RefreshAiModelsCommand.php       # Artisan command for manual refresh
```

### Controller Update
```
src/Http/Controllers/
└── AiApiKeyController.php           # Updated getProviderModels() method
```

## Usage

### Automatic Model Loading
When you create or edit an API key:
1. The system validates your API key with the provider
2. On provider selection, models are fetched automatically
3. Models appear in the dropdown within 1-2 seconds
4. Selection is cached for 1 hour

### Manual Model Refresh
If you need to refresh models manually (e.g., after provider adds new models):

```bash
# Refresh models for all providers
php artisan ai:refresh-models

# Refresh models for a specific provider
php artisan ai:refresh-models --provider=1

# For OpenAI specifically (adjust ID as needed)
php artisan ai:refresh-models --provider=1
```

### PHP Usage (In Your Code)
```php
use Botble\AiAssistant\Services\ModelCacheService;

$cacheService = new ModelCacheService();
$provider = AiProvider::findOrFail(1); // OpenAI
$apiKey = "sk-..."; // Your API key

// Get models (cached for 1 hour)
$models = $cacheService->getModels($provider, $apiKey);

// Force refresh (bypass cache)
$models = $cacheService->getModels($provider, $apiKey, forceRefresh: true);

// Clear cache for a provider
$cacheService->clearCache($provider->id);

// Clear all model caches
$cacheService->clearAllCaches();
```

## API Response Format

When you request models via the API endpoint:
```
GET /admincp/ai-assistant/api-keys/get-models/{providerId}
```

Response:
```json
{
  "success": true,
  "provider_id": 1,
  "provider_name": "OpenAI",
  "models": [
    "gpt-4",
    "gpt-4-turbo",
    "gpt-3.5-turbo",
    "gpt-4-32k"
  ]
}
```

## Error Handling

### API Key Not Found
If no active API key exists for a provider:
- Models are fetched from previously used models in database
- No API call is made
- User sees cached or historical models

### API Call Fails
If the provider's API is down or authentication fails:
- System logs warning with error message
- Returns empty array (no crash)
- Frontend gracefully handles empty models list
- User can still save form with previously selected model

### Invalid API Key
If API key is invalid:
- Provider's API returns 401 Unauthorized
- System catches exception and logs error
- User is guided to validate their API key

## Security Considerations

1. **API Key Encryption**: Keys are encrypted in database, decrypted only when needed
2. **Session-Only Decryption**: Keys are decrypted on-the-fly, never stored unencrypted in memory
3. **Scope Limiting**: Model fetcher only calls models endpoint, not other APIs
4. **Rate Limiting**: Caching prevents excessive API calls (1 per provider per hour max)

## Troubleshooting

### Models Not Appearing
1. **Check API Key**: Verify your API key is correct and active
2. **Check Provider Status**: Ensure provider is marked as active in settings
3. **Check Logs**: Look in `storage/logs/laravel-*.log` for errors
4. **Refresh Cache**: Run `php artisan ai:refresh-models`
5. **Provider Availability**: Check if provider's API is accessible from your server

### Slow Model Loading
1. **Cache Issue**: If first load is slow, subsequent loads should be instant (1-hour cache)
2. **Network Issue**: If you have slow internet, API calls may take longer
3. **Provider Slowness**: Provider's API might be slow; check their status page

### Missing Models
1. **Provider Limit**: Some providers have separate APIs for different model categories
2. **Permissions**: Your API key might not have access to all models
3. **Account Type**: Free-tier accounts may not have access to all models
4. **Regional Availability**: Some models may not be available in your region

## Provider-Specific Notes

### OpenAI
- Automatically filters to GPT models (excludes embeddings, vision, etc.)
- Requires valid `sk-...` API key
- Models updated automatically when released

### Anthropic (Claude)
- Returns all available Claude versions
- Requires valid Anthropic API key
- Uses `anthropic-version: 2023-06-01` header

### Google Gemini
- Extracts model IDs from full resource paths
- Requires valid Google AI API key
- Free tier available but limited

### DeepSeek
- Returns all DeepSeek models (chat, reasoning, etc.)
- Requires valid DeepSeek API key
- Growing model catalog

### Grok (xAI)
- Returns Grok models from xAI's API
- Requires valid xAI API key
- Latest reasoning models included

### OpenRouter
- Returns all models available through OpenRouter
- Requires valid OpenRouter API key
- Most comprehensive model catalog

## Future Enhancements

Potential improvements:
- [ ] Per-provider rate limiting
- [ ] Model filtering by capability (vision, code, reasoning)
- [ ] Model cost comparison display
- [ ] Automatic model recommendations based on use case
- [ ] Webhook integration for model updates
- [ ] Model usage analytics by provider

## Support

For issues with model fetching:
1. Check the troubleshooting section above
2. Review logs in `storage/logs/`
3. Verify API key validity with the provider directly
4. Run `php artisan ai:refresh-models` to manually refresh

---

**Version**: 1.0  
**Last Updated**: January 28, 2026
