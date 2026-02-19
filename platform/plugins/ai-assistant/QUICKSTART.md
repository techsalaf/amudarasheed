# 🚀 AI Assistant - Quick Start (5 Minutes)

## 1️⃣ Install (30 seconds)

```bash
php artisan migrate
```

Done! The plugin auto-registers and loads migrations.

## 2️⃣ Get API Keys (2 minutes)

Pick your provider(s):

| Provider | Link | Best For |
|----------|------|----------|
| **OpenAI** | [Get Key](https://platform.openai.com/api-keys) | Best quality (GPT-4) |
| **Gemini** | [Get Key](https://aistudio.google.com/app/apikey) | Free tier, fast |
| **Claude** | [Get Key](https://console.anthropic.com/keys) | Long context |
| **DeepSeek** | [Get Key](https://platform.deepseek.com/) | Cost-effective |
| **OpenRouter** | [Get Key](https://openrouter.ai/keys) | Many models in one |
| **Grok** | [Get Key](https://console.x.ai/) | Latest from xAI |

**Pro tip**: Get 2-3 keys for automatic fallback if one fails!

## 3️⃣ Configure in Admin (2 minutes)

1. **Log in** to admin panel
2. **Go to**: Tools > **AI Assistant** > Settings
3. **Check boxes**:
   - ✅ Enable AI Assistant
   - ✅ Enable for Posts/Pages/Products
   - ✅ Enable Text Generation
4. **Click**: Save

## 4️⃣ Add Your API Key (30 seconds)

1. **Go to**: Tools > **AI Assistant** > API Keys
2. **Click**: Add New Key
3. **Fill in**:
   - Provider: OpenAI (or your choice)
   - API Key: Paste from provider dashboard
   - Priority: 0 (tries first)
4. **Save**

## 5️⃣ Start Generating! (1 minute)

1. **Edit** any blog post/page/product
2. **Find** the field (title, description, etc.)
3. **Click**: 🪄 **Generate** button (next to field)
4. **In modal**:
   - Prompt: "Write a compelling product description"
   - Custom Instruction: (optional)
   - Click: **Generate**
5. **Done** - Content auto-populates! ✨

## Common Tasks

### 📝 Create Custom Tone Template

**Tools > AI Assistant > Custom Instructions**

Create "Professional" instruction:
```
Write in professional tone using sophisticated vocabulary.
Keep to 2-3 sentences. Focus on business value.
```

Now it appears in the Generate modal for all users!

### 📊 View Usage & Cost

**Tools > AI Assistant > Usage & Analytics**

See:
- Total requests generated
- Tokens consumed
- Estimated costs
- Error rates
- Export as CSV

### 🔄 Set Up Fallback

Add 2nd API key with priority 1:

1. Tools > API Keys > Add New Key
2. Provider: Claude (different from first)
3. Priority: **1** (tries if OpenAI fails)
4. Save

Now if OpenAI quota exhausts, it automatically tries Claude!

### ⚙️ Adjust Settings

**Tools > AI Assistant > Settings**

- **Max Tokens**: Increase for longer content
- **Temperature**: Higher = more creative (0.7-1.5)
- **PII Protection**: Enable if concerned about data privacy

## 🎯 Pro Tips

**💡 Tip 1: Custom Instructions**

Create instructions for consistent style:
- "SEO Optimized" - Include keywords naturally
- "Casual Tone" - Conversational, friendly
- "Technical" - Include specifications
- "Storytelling" - Narrative approach

**💡 Tip 2: Provider Priority**

Order by speed & cost:
1. Gemini (free, fast)
2. DeepSeek (cheap)
3. OpenAI (best quality)

**💡 Tip 3: Token Limits**

Set monthly limits to control costs:
- Development: 100k tokens/month
- Production: 1M tokens/month
- Adjust as needed

**💡 Tip 4: Batch Generation**

Need to generate for many posts?
```bash
# Programmatically (see Developer guide)
foreach ($posts as $post) {
    $result = $ai->generateText("Describe: {$post->title}");
    $post->update(['description' => $result->content]);
}
```

## 🆘 Troubleshooting

**Q: "Invalid API Key" error**
- ✅ Copy-paste key again from provider dashboard
- ✅ Check key is active (not expired/revoked)
- ✅ Ensure key has text-generation permission

**Q: "No available providers"**
- ✅ Add at least one API key
- ✅ Enable it in API Keys list
- ✅ Check it has remaining token quota

**Q: Slow generation**
- ✅ Reduce max_tokens in settings
- ✅ Try different provider (Gemini is faster)
- ✅ Check internet connection

**Q: How much will it cost?**
- ✅ Check **Usage & Analytics** page
- ✅ Costs vary: OpenAI ~$0.02/1k tokens
- ✅ Set **monthly_token_limit** to cap spending

## 📚 More Info

- **Full Setup**: [INSTALLATION.md](INSTALLATION.md)
- **For Developers**: [DEVELOPER.md](DEVELOPER.md)
- **Features & API**: [README.md](README.md)
- **Admin Panel**: Tools > AI Assistant (all features)

## ✨ You're Ready!

Generate amazing content with AI in your CMS:

```
Admin Panel 
→ Tools > AI Assistant > Settings ✓
→ Tools > AI Assistant > API Keys ✓
→ Edit Post/Page > Click Generate ✓
→ 🎉 Done!
```

**Need help?** Check the docs or your provider's API documentation.
