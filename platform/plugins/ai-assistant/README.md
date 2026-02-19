# AI Assistant Plugin for Botble CMS

A comprehensive AI-powered content generation plugin for Botble CMS supporting multiple AI providers with automatic fallback, usage tracking, and custom instructions.

## Features

✨ **Multiple AI Provider Support**
- OpenAI (GPT-4, GPT-3.5)
- Google Gemini
- Anthropic Claude
- DeepSeek
- OpenRouter
- Grok (xAI)

🎯 **Smart Features**
- Automatic fallback to next provider when quota exhausted
- Priority-based provider ordering (configurable via admin panel)
- Per-API-key token limits and usage tracking
- Monthly token reset
- Custom instructions/prompts for consistent tone and style
- PII protection (optional)

📊 **Admin Dashboard**
- API key management with encryption
- Provider prioritization
- Usage analytics and detailed logs
- CSV export for usage data
- Custom instruction templates
- Comprehensive settings panel

🚀 **Content Integration**
- Inline content generation with "Generate" buttons
- Support for:
  - Blog post titles, descriptions, content
  - Page titles and content
  - Product names, descriptions, details
  - SEO fields (meta title, meta description)
  - Custom fields
  - Media captions

⚙️ **Settings Management**
- Enable/disable by content type
- Configurable temperature and token limits
- Default model selection
- Per-provider priority
- Token limit enforcement
- Auto-reset options

## Installation

1. Copy the plugin to `platform/plugins/ai-assistant/`
2. Run migrations:
   ```bash
   php artisan migrate
   ```
3. Access admin panel at `/admin/ai-assistant/settings`

## Configuration

### Adding API Keys

1. Go to **Admin > AI Assistant > API Keys**
2. Click **Add New Key**
3. Select provider
4. Paste your API key (encrypted storage)
5. Set optional monthly token limit
6. Set priority (lower = tries first on fallback)
7. Save

### Custom Instructions

1. Go to **Admin > AI Assistant > Custom Instructions**
2. Create instructions like:
   - "Professional Tone"
   - "SEO Optimized"
   - "Friendly & Conversational"
   - "Technical Details"
3. These appear in generation modal for users

### Settings

1. Go to **Admin > AI Assistant > Settings**
2. Configure:
   - Default model/provider
   - Max tokens per request
   - Temperature (creativity level)
   - Content types to enable
   - PII protection
   - Usage tracking

## Usage

### For Admin Users

**Inline Generation:**
- Edit any supported content
- Click **Generate** button next to fields
- Enter prompt, select custom instruction
- Content auto-populates on success

**Track Usage:**
- View **Usage & Analytics** for:
  - Total requests/successes/failures
  - Token consumption
  - Response times
  - Cost tracking
  - Export to CSV

### For Developers

**Programmatic Generation:**

```php
use Botble\AiAssistant\Services\AiGenerationService;

$aiService = app(AiGenerationService::class);

// Generate text
$result = $aiService->generateText(
    prompt: "Write a compelling product description",
    options: ['temperature' => 0.7, 'max_tokens' => 500],
    customInstruction: "Professional tone, 2-3 sentences"
);

if ($result->success) {
    echo $result->content; // Generated text
    echo $result->getTotalTokens(); // Tokens used
} else {
    echo $result->error; // Error message
}
```

**Settings:**

```php
use Botble\AiAssistant\Services\AiSettingsService;

$settings = app(AiSettingsService::class);

// Get settings
$maxTokens = $settings->getSetting('max_tokens_per_request');
$isEnabled = $settings->getSetting('enable_ai');

// Update settings
$settings->updateSettings([
    'max_tokens_per_request' => 2000,
    'temperature' => 0.8,
    'enable_pii_protection' => false,
]);
```

**Frontend Integration:**

```html
<!-- Add button next to field -->
<textarea id="productDescription"></textarea>
<button type="button" class="btn btn-sm ai-generate-btn" 
    data-field-id="productDescription" 
    data-field-type="product_description">
    <i class="fas fa-wand-magic-sparkles"></i> Generate
</button>

<script src="/path/to/ai-assistant.js"></script>
<script>
    // Or programmatically:
    AiAssistant.addGenerateButton('productDescription', 'product_description');
</script>
```

## Fallback Logic

When generating content:

1. System checks available API keys (must be active)
2. Orders by priority (lower number = higher priority)
3. Tries first available key with sufficient tokens
4. On failure/quota exhaustion, tries next key
5. Returns error if all keys fail

**Example Priority Order:**
- Priority 0 (OpenAI GPT-4) → Try first
- Priority 1 (Claude Opus) → Try if first fails
- Priority 2 (Gemini Pro) → Try if second fails
- etc.

## Database Schema

### ai_providers
- Provider definitions (OpenAI, Gemini, Claude, etc.)
- Stored once, reusable

### ai_api_keys
- API keys (encrypted)
- Provider reference
- Model specification
- Token limits and usage
- Priority ordering

### ai_usage_logs
- All generation attempts
- Tokens consumed
- Success/failure status
- Response times
- User tracking
- Cost estimation

### ai_custom_instructions
- Reusable instruction templates
- Display order
- Active/inactive flag

### ai_settings
- Global plugin settings
- Dynamic configuration

## Permissions

The plugin creates these roles:
- `ai-assistant.access` - View AI Assistant
- `ai-assistant.generate` - Generate content
- `ai-assistant.settings` - Manage settings
- `ai-assistant.keys` - Manage API keys
- `ai-assistant.instructions` - Manage instructions
- `ai-assistant.usage` - View usage logs

## Security

✅ **API Key Encryption**
- All keys encrypted before storage
- Decrypted only when needed
- Never logged or exposed

✅ **PII Protection**
- Optional PII protection (enabled by default)
- Sanitization before sending to providers
- Can be disabled per provider

✅ **Rate Limiting**
- Per-key token limits
- Monthly reset support
- Prevents accidental overspending

## Troubleshooting

**API Key Invalid**
- Verify key is correct and active
- Check provider dashboard
- Ensure key has necessary permissions

**Quota Exceeded**
- Increase monthly token limit
- Reset token counter manually
- Add backup API keys

**Slow Responses**
- Check provider API status
- Reduce max tokens
- Try higher priority provider

## Future Enhancements

- [ ] Scheduled batch generation
- [ ] Cost prediction
- [ ] Advanced analytics dashboard
- [ ] A/B testing for content variations
- [ ] Webhook notifications
- [ ] Rate limiting per user
- [ ] Content moderation
- [ ] Language-specific templates

## Support

For issues or feature requests, contact the Botble team or contribute to the plugin repository.
