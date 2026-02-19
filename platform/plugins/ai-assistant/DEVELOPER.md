# AI Assistant - Developer Guide

## Architecture

### Plugin Structure

```
ai-assistant/
├── src/
│   ├── Drivers/              # AI provider implementations
│   │   ├── AiDriverInterface.php
│   │   ├── AiGenerationResult.php
│   │   ├── OpenAiDriver.php
│   │   ├── GeminiDriver.php
│   │   ├── ClaudeDriver.php
│   │   ├── DeepSeekDriver.php
│   │   ├── OpenRouterDriver.php
│   │   └── GrokDriver.php
│   ├── Models/               # Eloquent models
│   │   ├── AiProvider.php
│   │   ├── AiApiKey.php
│   │   ├── AiUsageLog.php
│   │   ├── AiCustomInstruction.php
│   │   └── AiSetting.php
│   ├── Services/             # Business logic
│   │   ├── AiGenerationService.php
│   │   └── AiSettingsService.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AiGenerationController.php
│   │       ├── AiApiKeyController.php
│   │       ├── AiSettingsController.php
│   │       ├── AiCustomInstructionController.php
│   │       └── AiUsageController.php
│   └── Providers/
│       └── AiAssistantServiceProvider.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── js/
│   └── lang/
├── routes/
│   └── web.php
├── config/
│   └── ai-assistant.php
├── plugin.json
└── composer.json
```

### Core Components

#### 1. Driver Layer (Pluggable)

**Interface: `AiDriverInterface`**

Each provider implements:
```php
public function generateText(string $prompt, array $options, ?string $customInstruction): AiGenerationResult
public function generateImage(string $prompt, array $options): AiGenerationResult
public function getModels(): array
public function validateApiKey(string $apiKey): bool
```

**Adding New Provider:**

```php
// src/Drivers/MyCustomDriver.php
class MyCustomDriver implements AiDriverInterface {
    protected string $apiKey;
    protected string $baseUrl = 'https://api.example.com';
    
    public function generateText(
        string $prompt,
        array $options = [],
        ?string $customInstruction = null
    ): AiGenerationResult {
        // Implementation
    }
    
    // ... other methods
}
```

Then register in `AiGenerationService->getDriver()`:

```php
protected function getDriver(AiApiKey $apiKey): ?AiDriverInterface {
    return match ($apiKey->provider->name) {
        'mycustom' => new MyCustomDriver($decryptedKey, $model),
        // ... others
    };
}
```

#### 2. Service Layer

**`AiGenerationService`**
- Manages text/image generation
- Handles fallback logic
- Logs usage
- Updates token counts

**`AiSettingsService`**
- Manages global settings
- Persists to database
- Type casting (boolean, json, etc.)

#### 3. Models

All models are standard Eloquent:

```php
// Usage example
$logs = AiUsageLog::with('apiKey.provider')
    ->where('status', 'success')
    ->get();

foreach ($logs as $log) {
    echo $log->getTotalTokens();
    echo $log->apiKey->provider->display_name;
}
```

## Request/Response Flow

### Text Generation Flow

```
Client (Admin UI)
    ↓
[POST] /admin/ai-assistant/api/generate-text
    ↓
AiGenerationController::generateText()
    ↓
AiGenerationService::generateText()
    ↓
1. Get available API keys (ordered by priority)
2. For each key:
   - Get driver instance
   - Call driver->generateText()
   - If success: Log usage, update tokens, return result
   - If fail: Log error, try next key
3. Return error if all fail
    ↓
JSON Response {
    success: boolean,
    content: string,
    tokens_used: integer,
    model: string,
    error: string (if failed)
}
    ↓
Client JavaScript
    ↓
Update DOM / Show modal
```

### Database Flow

```
AiApiKey (active, has tokens)
    ↓
Provider (driver factory)
    ↓
Driver instance
    ↓
Generate content
    ↓
Log AiUsageLog entry
    ↓
Update AiApiKey.monthly_tokens_used
```

## Extending the Plugin

### Add Custom Field Support

1. **In your controller/view:**

```blade
<textarea id="myCustomField"></textarea>
<button class="ai-generate-btn" data-field-id="myCustomField" data-field-type="my_custom">
    Generate
</button>
```

2. **The plugin detects the button automatically** via JavaScript

### Add Provider-Specific Logic

In `AiGenerationService`, customize options:

```php
public function generateText(
    string $prompt,
    array $options = [],
    ?string $customInstruction = null
): AiGenerationResult {
    // Add provider-specific options
    if (/* custom condition */) {
        $options['top_p'] = 0.9;  // OpenAI specific
        $options['presence_penalty'] = 0.5;
    }
    
    // ... rest of logic
}
```

### Custom Cost Calculation

Create a cost calculator:

```php
// src/Services/CostCalculationService.php
class CostCalculationService {
    public function calculateCost(
        AiApiKey $apiKey,
        int $inputTokens,
        int $outputTokens
    ): float {
        return match ($apiKey->model) {
            'gpt-4' => ($inputTokens * 0.00003) + ($outputTokens * 0.00006),
            'gpt-3.5-turbo' => ($inputTokens * 0.0000005) + ($outputTokens * 0.0000015),
            // ... other models
        };
    }
}
```

Update `AiUsageLog` creation:

```php
protected function logUsage(AiApiKey $apiKey, AiGenerationResult $result, ...): void {
    $cost = app(CostCalculationService::class)
        ->calculateCost($apiKey, $result->inputTokens, $result->outputTokens);
    
    AiUsageLog::create([
        // ... other fields
        'cost' => $cost,
    ]);
}
```

### Webhook Notifications

Add to `AiGenerationService`:

```php
protected function logUsage(...): void {
    // Existing logic
    
    // Send webhook
    if (AiSetting::get('enable_webhooks')) {
        $this->sendWebhook('generation.completed', [
            'model' => $result->model,
            'tokens' => $result->getTotalTokens(),
            'success' => $result->success,
        ]);
    }
}

protected function sendWebhook(string $event, array $data): void {
    $url = AiSetting::get('webhook_url');
    Http::post($url, ['event' => $event, 'data' => $data]);
}
```

### Rate Limiting

```php
// Middleware/AiRateLimiter.php
class AiRateLimiter {
    public function handle($request, Closure $next) {
        $user = auth()->user();
        $today = now()->toDateString();
        $key = "ai-requests:{$user->id}:{$today}";
        $limit = 100; // requests per day
        
        if (Cache::get($key, 0) >= $limit) {
            return response()->json([
                'success' => false,
                'error' => 'Daily generation limit exceeded'
            ], 429);
        }
        
        Cache::increment($key, 1, now()->endOfDay());
        return $next($request);
    }
}
```

## Testing

### Unit Tests

```php
// tests/Unit/AiGenerationServiceTest.php
use Botble\AiAssistant\Services\AiGenerationService;
use Tests\TestCase;

class AiGenerationServiceTest extends TestCase {
    public function test_generates_text_with_available_key() {
        // Setup
        $service = app(AiGenerationService::class);
        
        // Execute
        $result = $service->generateText('Write a title');
        
        // Assert
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->content);
    }
    
    public function test_fallback_to_next_provider() {
        // Test fallback logic
    }
}
```

### Integration Tests

```php
// tests/Feature/AiGenerationControllerTest.php
public function test_api_endpoint_generates_content() {
    $response = $this->post('/admin/ai-assistant/api/generate-text', [
        'prompt' => 'Write a product description',
        'max_tokens' => 500,
    ]);
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'content',
        'tokens_used',
        'model',
    ]);
}
```

## Performance Optimization

### Caching

Cache available keys and instructions:

```php
// In AiGenerationService
protected function getAvailableApiKeysInOrder(): Collection {
    return Cache::remember('ai-available-keys', 300, function () {
        return AiApiKey::with('provider')
            ->where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->filter(fn($k) => $k->hasTokensAvailable());
    });
}
```

### Queue Long Requests

```php
// config/ai-assistant.php
'queue_long_requests' => true,
'request_timeout_seconds' => 30,

// In controller
if ($promptLength > 1000) {
    GenerateContentJob::dispatch($request->validated());
    return response()->json(['queued' => true]);
}
```

### Database Indexing

Migrations include strategic indexes:
- `api_key_id` on usage_logs (filter logs by key)
- `created_at` on usage_logs (date range queries)
- `is_active` on api_keys (find active keys quickly)

## API Monitoring

### Track Provider Health

```php
// src/Services/ProviderHealthService.php
class ProviderHealthService {
    public function check(AiApiKey $key): array {
        $driver = $this->getDriver($key);
        
        $result = $driver->generateText('test', ['max_tokens' => 10]);
        
        return [
            'is_healthy' => $result->success,
            'latency_ms' => $result->responseTimeMs,
            'error' => $result->error,
            'checked_at' => now(),
        ];
    }
}
```

### Cost Budgeting

```php
// Track spending
$monthlyCost = AiUsageLog::whereBetween('created_at', [
    now()->startOfMonth(),
    now()->endOfMonth(),
])->sum('cost');

if ($monthlyCost > $budget) {
    // Alert or disable features
}
```

## Debugging

### Enable Logging

```php
// .env
AI_ASSISTANT_DEBUG=true

// In service
if (config('ai-assistant.debug')) {
    Log::info('AI Generation', [
        'prompt' => $prompt,
        'provider' => $apiKey->provider->name,
        'result' => $result->toArray(),
    ]);
}
```

### API Response Inspection

```php
// In driver
if (!$response->successful()) {
    Log::error('Provider API Error', [
        'status' => $response->status(),
        'body' => $response->body(),
        'headers' => $response->headers(),
    ]);
}
```

## Deployment

### Production Checklist

- [ ] Encrypt all API keys (Laravel encryption)
- [ ] Set strong encryption keys in `.env`
- [ ] Configure role-based access
- [ ] Set up webhooks for monitoring
- [ ] Enable PII protection
- [ ] Set reasonable token limits
- [ ] Monitor costs daily
- [ ] Backup database regularly
- [ ] Test fallback providers
- [ ] Set up error alerts

### Environment Variables

```bash
# .env
AI_ASSISTANT_ENABLED=true
AI_ASSISTANT_DEBUG=false
AI_ASSISTANT_WEBHOOK_URL=https://example.com/webhooks
```

## Contributing

To add support for a new provider:

1. Create driver class in `src/Drivers/{Provider}Driver.php`
2. Implement `AiDriverInterface`
3. Add tests
4. Update `AiGenerationService->getDriver()`
5. Add provider to seed in `AiAssistantServiceProvider`
6. Submit PR

## FAQ

**Q: Can I use multiple API keys per provider?**
A: Yes, set different priorities to fallback between them.

**Q: How do I limit per-user generation?**
A: Use middleware + Cache to track requests per user per day.

**Q: Can I cache generated content?**
A: Yes, cache `AiGenerationResult` keyed by prompt hash.

**Q: How do I track ROI?**
A: Compare `monthly_tokens_used * cost_per_token` with engagement metrics.

**Q: Can tokens reset midmonth?**
A: Yes, via `AiApiKey->tokens_reset_at` and `update('monthly_tokens_used', 0)`.
