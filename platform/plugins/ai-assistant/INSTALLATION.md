# AI Assistant - Installation & Setup Guide

## Quick Start

### Prerequisites
- Botble CMS 12+ 
- PHP 8.1+
- At least one AI provider API key (OpenAI, Gemini, Claude, etc.)

### Step 1: Install Plugin

The plugin is already in `platform/plugins/ai-assistant/`. Just run migrations:

```bash
php artisan migrate
```

### Step 2: Get API Keys

Choose your AI providers and get API keys:

| Provider | URL | Notes |
|----------|-----|-------|
| OpenAI | https://platform.openai.com/api-keys | GPT-4, GPT-3.5 |
| Google Gemini | https://aistudio.google.com/app/apikey | Free tier available |
| Claude | https://console.anthropic.com/keys | Anthropic models |
| DeepSeek | https://platform.deepseek.com/ | Cost-effective |
| OpenRouter | https://openrouter.ai/keys | Aggregates multiple models |
| Grok | https://console.x.ai/ | xAI's latest model |

### Step 3: Configure Plugin

1. **Log in to admin panel**
2. **Go to Tools > AI Assistant > Settings**
3. **Configure:**
   - ✅ Enable AI Assistant
   - ✅ Select content types (posts, pages, products, SEO, custom fields)
   - ✅ Set max tokens and temperature
   - ✅ Enable PII protection if needed

### Step 4: Add API Keys

1. **Go to Tools > AI Assistant > API Keys**
2. **Click "Add New Key"**
3. **Fill in:**
   - Provider (OpenAI, Gemini, Claude, etc.)
   - API Key (from provider dashboard)
   - Model (optional, uses provider default if empty)
   - Priority (lower = tries first on fallback)
   - Monthly token limit (optional)
4. **Save**

### Step 5: Create Custom Instructions (Optional)

1. **Go to Tools > AI Assistant > Custom Instructions**
2. **Click "Create New"**
3. **Examples:**
   - Professional Tone
   - SEO Optimized
   - Friendly & Casual
   - Technical Details
4. **These appear in generation modals**

## Usage

### For Content Creators

#### Inline Generation

When editing any supported content:

1. **Click "Generate" button** next to the field
2. **In modal:**
   - Enter your **prompt** (describe what you want)
   - Select **custom instruction** (optional tone/style)
   - Adjust **temperature** (0.7 default, 0=deterministic, 2=creative)
   - Set **max tokens** (1000 default)
3. **Click "Generate"**
4. **Content auto-populates** on success

#### Supported Fields

- Blog Posts: title, description, content, excerpt
- Pages: title, content
- Products: name, description, details
- SEO: meta title, meta description, keywords
- Media: captions, alt text
- Custom fields: any text area

### For Administrators

#### Monitor Usage

1. **Go to Tools > AI Assistant > Usage & Analytics**
2. **View:**
   - Total requests and successes
   - Token consumption
   - Response times
   - Cost tracking
3. **Export to CSV** for reporting

#### Manage API Keys

1. **View active keys** and usage
2. **Enable/disable** keys without deletion
3. **Reorder priority** for fallback preference
4. **Reset token counters** manually
5. **Update** key limits anytime

#### Manage Providers

Settings page allows:
- **Default provider** selection
- **Max tokens per request** (system-wide limit)
- **Temperature** (controls creativity)
- **PII protection** (optional anonymization)
- **Auto token reset** (monthly)

## API Reference

### Programmatic Usage

#### Generate Text

```php
use Botble\AiAssistant\Services\AiGenerationService;

$ai = app(AiGenerationService::class);

$result = $ai->generateText(
    prompt: "Write a product description for a blue wireless headphone",
    options: [
        'temperature' => 0.7,
        'max_tokens' => 1500,
    ],
    customInstruction: "Professional tone, 3-4 sentences, include features"
);

if ($result->success) {
    echo $result->content;  // Generated text
    echo $result->inputTokens;  // Tokens used
    echo $result->outputTokens;
} else {
    echo $result->error;
}
```

#### Generate Image

```php
$result = $ai->generateImage(
    prompt: "A serene landscape with mountains and a lake at sunset",
    options: [
        'size' => '1024x1024',
    ]
);

if ($result->success) {
    $imageUrl = $result->content;  // URL to generated image
}
```

#### Access Settings

```php
use Botble\AiAssistant\Services\AiSettingsService;

$settings = app(AiSettingsService::class);

$maxTokens = $settings->getSetting('max_tokens_per_request');
$enablePii = $settings->getSetting('enable_pii_protection');

// Update
$settings->updateSettings([
    'enable_ai' => true,
    'max_tokens_per_request' => 2000,
]);
```

#### Usage Logs

```php
use Botble\AiAssistant\Models\AiUsageLog;

// Get logs
$logs = AiUsageLog::where('status', 'success')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();

// Statistics
$totalTokens = $logs->sum(fn($log) => $log->getTotalTokens());
$avgResponseTime = $logs->avg('response_time_ms');
```

## Fallback Strategy

When generating content:

```
Request → Check Default Provider
             ↓
          Has Tokens? 
             ↓ Yes
          Try Generation
             ↓
          Success? → Return Content
             ↓ No
          Try Next Priority Provider
             ↓
          No More Providers? → Return Error
```

**Priority Management:**

Set via **Settings > Provider Priority** (admin panel):
- Drag to reorder
- Lower number = higher priority
- Auto-checks token availability

## Token Management

### How Tokens Work

- **Input tokens**: Your prompt length
- **Output tokens**: Generated content length
- **Cost**: Varies by provider and model

### Monthly Limits

Per API key:
- Set **monthly limit** (optional)
- System tracks **monthly usage**
- Reset **auto-monthly** or manually
- **Prevents** overspending

### Monitoring

Usage page shows:
- Total tokens consumed
- Cost per provider
- Success rate
- Average response time

## Security & Privacy

### API Key Encryption

✅ Keys are **encrypted in database**
✅ Decrypted only when generating content
✅ Never logged or displayed

### PII Protection

Enable via **Settings** to prevent sending:
- Email addresses
- Phone numbers
- Names
- Addresses
- Credit cards
- Personal data

### Permissions

Assign roles to control access:
- `ai-assistant.access` - View module
- `ai-assistant.generate` - Generate content
- `ai-assistant.settings` - Manage settings
- `ai-assistant.keys` - Manage API keys
- `ai-assistant.instructions` - Create instructions
- `ai-assistant.usage` - View analytics

## Troubleshooting

### "Invalid API Key"

❌ Check if:
- Key is correct in provider dashboard
- Key hasn't been revoked/disabled
- Key has proper permissions
- Provider is working

### "No Available Providers"

❌ Check if:
- At least one key is **enabled** and **active**
- Keys have **token quota remaining**
- Provider is **online** (check status page)

### Slow Generation

❌ Try:
- Reduce `max_tokens` setting
- Use higher priority/faster provider
- Check network connection
- Check provider API status

### High Token Usage

❌ Reduce:
- `max_tokens` per request
- Prompt verbosity
- Regeneration requests

## Cost Estimation

**Monthly costs depend on:**
- Number of requests
- Tokens per request
- Provider pricing

### Example (OpenAI GPT-4)

- Input: $0.03 per 1K tokens
- Output: $0.06 per 1K tokens

100 requests × 500 tokens = 50K tokens
Cost: ~$1.50-$3.00/month

**Recommendation:** Monitor usage regularly via analytics.

## Advanced Configuration

### Custom Fallback Order

Set provider **priority** in settings:

```
1. OpenAI (priority: 0)    ← Tries first
2. Claude (priority: 1)    ← If OpenAI fails/quota
3. Gemini (priority: 2)    ← If Claude fails/quota
```

### Per-Field Customization

Via Laravel:

```php
// In your blade template
<textarea id="productDesc"></textarea>
<script>
    AiAssistant.addGenerateButton('productDesc', 'product_description');
</script>
```

### Batch Generation

```php
$posts = Post::where('description', null)->get();

foreach ($posts as $post) {
    $result = $ai->generateText("Describe: {$post->title}");
    $post->update(['description' => $result->content]);
}
```

## Support & Documentation

- **Plugin Folder**: `platform/plugins/ai-assistant/`
- **README**: `platform/plugins/ai-assistant/README.md`
- **Admin Panel**: Tools > AI Assistant
- **Status Page**: Usage & Analytics

## Updates & Maintenance

### Update Plugin

```bash
composer update botble/ai-assistant
php artisan migrate --path=platform/plugins/ai-assistant/database/migrations
npm run dev  # Rebuild JS
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:cache
```

### Monitor Health

Check Usage page monthly:
- Review token consumption
- Check error rates
- Review costs
- Adjust limits as needed

## Next Steps

1. ✅ **Get API keys** from providers
2. ✅ **Add keys** in admin panel
3. ✅ **Create custom instructions** for consistency
4. ✅ **Configure settings** for your needs
5. ✅ **Train team** on usage
6. ✅ **Monitor costs** via analytics
