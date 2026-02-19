# ✅ AI Assistant Setup Checklist

Use this checklist to set up the plugin step-by-step.

## Phase 1: Installation ⚙️

- [ ] Plugin files copied to `platform/plugins/ai-assistant/`
- [ ] Run `php artisan migrate`
- [ ] Database tables created (check via admin panel or DB)
- [ ] Provider records seeded (6 providers: OpenAI, Gemini, Claude, DeepSeek, OpenRouter, Grok)
- [ ] Default settings initialized
- [ ] Plugin appears in **Admin > Tools > AI Assistant**

**Verify**: Go to admin panel → Tools > AI Assistant > Settings (should load without errors)

---

## Phase 2: API Keys Setup 🔑

### OpenAI (Recommended for Beginners)

- [ ] Visit https://platform.openai.com/api-keys
- [ ] Create new API key
- [ ] Copy key
- [ ] Go to **Admin > Tools > AI Assistant > API Keys**
- [ ] Click **Add New Key**
- [ ] Select Provider: **OpenAI**
- [ ] Paste API key
- [ ] Set Model: **gpt-4** (or gpt-3.5-turbo for cost savings)
- [ ] Priority: **0** (highest)
- [ ] Save

### (Optional) Add Backup Provider

- [ ] Visit https://aistudio.google.com/app/apikey (Gemini)
- [ ] Create/copy API key
- [ ] Go to **Admin > Tools > AI Assistant > API Keys**
- [ ] Click **Add New Key**
- [ ] Select Provider: **Gemini**
- [ ] Paste API key
- [ ] Set Model: **gemini-pro**
- [ ] Priority: **1** (fallback)
- [ ] Save

**Verify**: You have at least 1 API key with "Active" status

---

## Phase 3: Configuration 🎛️

- [ ] Go to **Admin > Tools > AI Assistant > Settings**
- [ ] Enable features:
  - [ ] ✅ Enable AI Assistant
  - [ ] ✅ Enable Text Generation
  - [ ] ⚪ Enable Image Generation (optional)
- [ ] Enable content types:
  - [ ] ✅ Blog Posts
  - [ ] ✅ Pages
  - [ ] ✅ Products (if using)
  - [ ] ✅ SEO Fields
  - [ ] ✅ Custom Fields
- [ ] Set limits:
  - [ ] Max Tokens per Request: **1000** (adjust as needed)
  - [ ] Temperature: **0.7** (balanced creativity)
- [ ] Security:
  - [ ] ✅ Enable PII Protection
  - [ ] ✅ Enable Usage Tracking
- [ ] Token management:
  - [ ] ✅ Auto Reset Tokens Monthly
- [ ] Click **Save Settings**

**Verify**: Settings save without errors

---

## Phase 4: Custom Instructions (Optional but Recommended) 📝

Create tone templates for consistent output:

### Template 1: Professional

- [ ] Go to **Admin > Tools > AI Assistant > Custom Instructions**
- [ ] Click **Add New Instruction**
- [ ] **Name**: Professional Tone
- [ ] **Instruction**:
  ```
  Write in professional, formal tone using sophisticated vocabulary.
  Maintain corporate and authoritative voice. Use proper grammar.
  Keep content concise and impactful.
  ```
- [ ] **Order**: 1
- [ ] ✅ Active
- [ ] Save

### Template 2: Friendly

- [ ] Click **Add New Instruction**
- [ ] **Name**: Friendly & Casual
- [ ] **Instruction**:
  ```
  Write in friendly, conversational tone. Use simple language,
  contractions, and a warm approach. Make it engaging and approachable.
  ```
- [ ] **Order**: 2
- [ ] ✅ Active
- [ ] Save

### Template 3: SEO Optimized

- [ ] Click **Add New Instruction**
- [ ] **Name**: SEO Optimized
- [ ] **Instruction**:
  ```
  Include relevant keywords naturally. Write compelling content.
  Use clear structure with proper emphasis. Focus on search
  engine visibility while maintaining readability.
  ```
- [ ] **Order**: 3
- [ ] ✅ Active
- [ ] Save

**Verify**: You see all 3 instructions in the list

---

## Phase 5: Test Content Generation ✨

### Test 1: Blog Post Title

- [ ] Go to **Blog > Posts** (or create new)
- [ ] Find the **Title** field
- [ ] Look for 🪄 **Generate** button
- [ ] Click it
- [ ] In modal:
  - [ ] Prompt: "Generate a catchy blog title about AI in marketing"
  - [ ] Custom Instruction: "Professional Tone"
  - [ ] Temperature: 0.7
  - [ ] Max Tokens: 100
- [ ] Click **Generate**
- [ ] ✅ Wait for content to appear
- [ ] Title field auto-populated with generated content
- [ ] Click **Save** on modal (auto-closes)

**Verify**: Title is populated with AI-generated content

### Test 2: Product Description

- [ ] Go to **Products** (or create new)
- [ ] Find **Description** field
- [ ] Click 🪄 **Generate**
- [ ] In modal:
  - [ ] Prompt: "Write a description for a wireless Bluetooth speaker"
  - [ ] Custom Instruction: "Friendly & Casual"
  - [ ] Click **Generate**
- [ ] ✅ Content appears in field

**Verify**: Description populated successfully

### Test 3: SEO Meta Description

- [ ] In same product/post editor
- [ ] Find **SEO Meta Description** field
- [ ] Click 🪄 **Generate**
- [ ] Prompt: "Create SEO meta description for this product"
- [ ] Custom Instruction: "SEO Optimized"
- [ ] Click **Generate**
- [ ] ✅ Meta description populated

**Verify**: SEO field populated

---

## Phase 6: Monitor Usage 📊

- [ ] Go to **Admin > Tools > AI Assistant > Usage & Analytics**
- [ ] Verify:
  - [ ] Total Requests: 3 (from tests above)
  - [ ] Successful: 3
  - [ ] Failed: 0
  - [ ] Total Tokens shows usage
- [ ] Filter options work:
  - [ ] Date range filtering
  - [ ] Status filtering
  - [ ] Provider filtering
- [ ] Click **Export CSV**
  - [ ] File downloads successfully
  - [ ] Contains your test generation data

**Verify**: All 3 test generations logged in analytics

---

## Phase 7: Team Training (Optional) 👥

For each team member using AI Assistant:

- [ ] Explain the 🪄 **Generate** button
- [ ] Show how to:
  - [ ] Enter a clear prompt
  - [ ] Select custom instruction for tone
  - [ ] Review generated content
  - [ ] Edit if needed
  - [ ] Save post/page
- [ ] Discuss best practices:
  - [ ] Clear, specific prompts work better
  - [ ] Generated content should be reviewed
  - [ ] Custom instructions ensure consistent style
  - [ ] Monitor usage costs via analytics
- [ ] Show them **Usage & Analytics** page

---

## Phase 8: Production Optimization 🚀

- [ ] Set reasonable **monthly token limits** per API key
  - Prevent unexpected overspending
- [ ] Monitor costs weekly via **Usage & Analytics**
- [ ] Adjust **max_tokens_per_request** if needed
  - Lower for faster, cheaper generation
  - Higher for more detailed content
- [ ] Review **PII Protection** is enabled
- [ ] Ensure **Usage Tracking** is enabled for auditing
- [ ] Set up provider **fallback ordering**:
  - Priority 0: Preferred (e.g., OpenAI)
  - Priority 1: Backup (e.g., Gemini)
  - Priority 2: Last resort (e.g., DeepSeek)
- [ ] Consider API key rotation schedule (every 3-6 months)
- [ ] Back up database regularly

---

## Phase 9: Advanced Configuration (Optional) 🔧

- [ ] Configure **role-based permissions**:
  - Admin: Full access
  - Editor: Generate content, view usage
  - Author: Generate on their posts only
- [ ] Set up **provider priority reordering** via Settings
- [ ] Create **specialized instructions** for your brand
- [ ] Consider **usage alerts** (check monthly totals)
- [ ] Explore **cost optimization**:
  - Use cheaper providers for simple tasks
  - Reserve premium providers for quality content

---

## 🎉 You're Done!

Your AI Assistant is now ready to use. Here's a quick reference:

### For Content Creators
- Click 🪄 **Generate** button next to any field
- Review generated content before saving
- Use custom instructions for consistent tone

### For Administrators
- Monitor **Usage & Analytics** monthly
- Manage API keys in **API Keys** section
- Adjust settings in **Settings** as needed
- Create custom instructions in **Custom Instructions**

### Common Paths

**Quick Generation**:
Post Editor → Click Generate → Enter prompt → Click Generate → Save

**Monitor Costs**:
Admin > Tools > AI Assistant > Usage & Analytics

**Configure Settings**:
Admin > Tools > AI Assistant > Settings

**Manage Keys**:
Admin > Tools > AI Assistant > API Keys

---

## ❓ Stuck?

Refer to:
- **Quick answers**: [QUICKSTART.md](QUICKSTART.md)
- **Detailed setup**: [INSTALLATION.md](INSTALLATION.md)
- **Troubleshooting**: See INSTALLATION.md > Troubleshooting section
- **Development**: [DEVELOPER.md](DEVELOPER.md)
- **API reference**: [README.md](README.md)

---

**Last Updated**: January 21, 2026
**Plugin Version**: 1.0.0
**Status**: ✅ Production Ready
