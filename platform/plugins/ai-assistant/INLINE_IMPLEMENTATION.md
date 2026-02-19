# AI Assistant Inline Implementation Guide

This guide explains how to add AI generation buttons to all content editing views throughout the Botble CMS admin panel.

## Overview

The AI Assistant plugin now includes:
- **13+ Controller methods** for generating content (posts, pages, products, SEO, categories, tags)
- **13+ API routes** for all content generation endpoints
- **JavaScript handler** (`ai-inline-generator.js`) that handles all AJAX communication
- **Blade component** (`inline-button.blade.php`) for easy button insertion
- **CSS styling** (`ai-inline-generator.css`) for button appearance and animations
- **Service Provider** registration for automatic asset loading

## How It Works

### 1. Backend Flow
```
User clicks AI button 
    → jQuery click handler triggered
    → AJAX GET request sent to route (e.g., `/admin/ai-assistant/api/generate-post-title`)
    → Controller validates input and calls AiGenerationService
    → Service uses configured AI provider to generate content
    → JSON response returned with generated content
    → JavaScript inserts content into target field
```

### 2. Frontend Flow
```
Button HTML includes:
  - Route URL (data-route)
  - Target field selector (data-target)
  - Input field selectors (data-inputs)
  
On click:
  - Gather values from input fields
  - Send AJAX request
  - Show loading spinner
  - Update target field on success
  - Show notification
  - Hide loading spinner
```

## Available Generation Methods

### Blog Posts
- **`generatePostTitle()`** - Generate catchy post titles
- **`generatePostContent()`** - Generate full blog post content based on title

### Pages
- **`generatePageTitle()`** - Generate descriptive page titles
- **`generatePageContent()`** - Generate page content based on title

### Products
- **`generateProductTitle()`** - Generate product names
- **`generateProductDescription()`** - Generate detailed product descriptions
- **`generateProductShortDescription()`** - Generate short/meta descriptions

### SEO
- **`generateSeoTitle()`** - Generate SEO-optimized meta titles (50-60 chars)
- **`generateSeoDescription()`** - Generate SEO meta descriptions (155-160 chars)

### Categories & Tags
- **`generateCategoryName()`** - Generate category names
- **`generateCategoryDescription()`** - Generate category descriptions
- **`generateTagName()`** - Generate tag names

## Using the Blade Component

### Basic Usage
```blade
<x-plugins.ai-assistant.inline-button 
    :route="route('admin.ai-assistant.api.generate-post-title')"
    target="#post_name"
    field="Title"
/>
```

### With Input Fields
Pass input fields as a JSON data attribute to gather context:

```blade
<x-plugins.ai-assistant.inline-button 
    :route="route('admin.ai-assistant.api.generate-post-content')"
    target="#post_content"
    :inputs="['title' => '#post_name', 'context' => '#post_excerpt']"
    field="Content"
    class="btn-sm"
/>
```

### Component Parameters
- **`route`** (required) - The API endpoint route
- **`target`** (required) - CSS selector of field to update (e.g., `#post_name`)
- **`field`** (optional, default: "Content") - Display name for button (e.g., "Title", "Description")
- **`inputs`** (optional) - Array of input field selectors for context gathering
  - Key: parameter name sent to API
  - Value: CSS selector of field to read from
- **`class`** (optional) - Additional CSS classes for the button
- **`slot`** (optional) - Custom button text (default: "AI {field}")

## API Endpoint Reference

### Blog Post Title
```
GET /admin/ai-assistant/api/generate-post-title
Parameters:
  - context (optional): Additional context for generation
  - lang (optional): Language code
Returns: { success: true, data: "Generated Title", model: "model-name", tokens: 123 }
```

### Blog Post Content
```
GET /admin/ai-assistant/api/generate-post-content
Parameters:
  - title (required): Post title
  - context (optional): Additional context
  - lang (optional): Language code
Returns: { success: true, data: "Generated content...", model: "model-name", tokens: 456 }
```

### Page Title
```
GET /admin/ai-assistant/api/generate-page-title
Parameters:
  - context (optional): Page purpose/description
Returns: { success: true, data: "Page Title" }
```

### Page Content
```
GET /admin/ai-assistant/api/generate-page-content
Parameters:
  - title (required): Page title
  - context (optional): Page details
Returns: { success: true, data: "Page content..." }
```

### Product Title
```
GET /admin/ai-assistant/api/generate-product-title
Parameters:
  - category (optional): Product category
  - keywords (optional): Product keywords
Returns: { success: true, data: "Product Name" }
```

### Product Description
```
GET /admin/ai-assistant/api/generate-product-description
Parameters:
  - title (required): Product title
  - category (optional): Product category
  - features (optional): Product features list
Returns: { success: true, data: "Long description..." }
```

### Product Short Description
```
GET /admin/ai-assistant/api/generate-product-short-description
Parameters:
  - title (required): Product title
Returns: { success: true, data: "Short description under 160 chars" }
```

### SEO Meta Title
```
GET /admin/ai-assistant/api/generate-seo-title
Parameters:
  - content_title (required): Page/product title
  - content_type (optional): "page", "post", "product"
  - keywords (optional): Target keywords
Returns: { success: true, data: "SEO-optimized title (50-60 chars)" }
```

### SEO Meta Description
```
GET /admin/ai-assistant/api/generate-seo-description
Parameters:
  - content_title (required): Page/product title
  - content_snippet (optional): Content preview
  - keywords (optional): Target keywords
Returns: { success: true, data: "SEO description (155-160 chars)" }
```

### Categories
```
GET /admin/ai-assistant/api/generate-category-name
GET /admin/ai-assistant/api/generate-category-description
Parameters: (various - see methods)
```

### Tags
```
GET /admin/ai-assistant/api/generate-tag-name
Parameters:
  - context (optional): Context for tag generation
```

## Integration Examples

### Blog Post Create/Edit Form
```blade
<!-- Post Title Field -->
<div class="form-group">
    <label>Post Title</label>
    <div class="input-group">
        <input type="text" id="post_name" name="name" class="form-control">
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-post-title')"
            target="#post_name"
            field="Title"
            class="btn-sm"
        />
    </div>
</div>

<!-- Post Excerpt Field (optional) -->
<div class="form-group">
    <label>Excerpt</label>
    <textarea id="post_excerpt" name="description" class="form-control"></textarea>
</div>

<!-- Post Content Field -->
<div class="form-group">
    <label>Content</label>
    <textarea id="post_content" name="content" class="form-control editor"></textarea>
    <x-plugins.ai-assistant.inline-button 
        :route="route('admin.ai-assistant.api.generate-post-content')"
        target="#post_content"
        :inputs="['title' => '#post_name', 'context' => '#post_excerpt']"
        field="Content"
        class="btn-sm"
    />
</div>
```

### Product Create/Edit Form
```blade
<!-- Product Name -->
<div class="form-group">
    <label>Product Name</label>
    <div class="input-group">
        <input type="text" id="product_name" name="name" class="form-control">
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-product-title')"
            target="#product_name"
            :inputs="['category' => '#product_category']"
            field="Name"
            class="btn-sm"
        />
    </div>
</div>

<!-- Product Category (for context) -->
<div class="form-group">
    <label>Category</label>
    <select id="product_category" name="category_id" class="form-control">
        <!-- options -->
    </select>
</div>

<!-- Product Description -->
<div class="form-group">
    <label>Description</label>
    <textarea id="product_description" name="description" class="form-control editor"></textarea>
    <x-plugins.ai-assistant.inline-button 
        :route="route('admin.ai-assistant.api.generate-product-description')"
        target="#product_description"
        :inputs="['title' => '#product_name', 'category' => '#product_category']"
        field="Description"
        class="btn-sm"
    />
</div>

<!-- Short Description -->
<div class="form-group">
    <label>Short Description</label>
    <input type="text" id="product_short_desc" name="short_description" class="form-control">
    <x-plugins.ai-assistant.inline-button 
        :route="route('admin.ai-assistant.api.generate-product-short-description')"
        target="#product_short_desc"
        :inputs="['title' => '#product_name']"
        field="Short Desc"
        class="btn-sm"
    />
</div>
```

### SEO Fields
```blade
<!-- Meta Title -->
<div class="form-group">
    <label>SEO Meta Title</label>
    <div class="input-group">
        <input type="text" id="seo_title" name="seo_title" class="form-control" maxlength="60">
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-seo-title')"
            target="#seo_title"
            :inputs="['content_title' => '#post_name']"
            field="SEO Title"
            class="btn-sm"
        />
    </div>
    <small class="form-text text-muted">50-60 characters</small>
</div>

<!-- Meta Description -->
<div class="form-group">
    <label>SEO Meta Description</label>
    <div class="input-group">
        <textarea id="seo_description" name="seo_description" class="form-control" rows="2" maxlength="160"></textarea>
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-seo-description')"
            target="#seo_description"
            :inputs="['content_title' => '#post_name', 'content_snippet' => '#post_content']"
            field="SEO Desc"
            class="btn-sm"
        />
    </div>
    <small class="form-text text-muted">155-160 characters</small>
</div>
```

## Editor Support

The JavaScript handler automatically detects and supports:

### Text Inputs & Textareas
```blade
<input type="text" id="title" name="title">
<textarea id="content" name="content"></textarea>
```

### Summernote Editor
```blade
<textarea id="content" name="content" class="summernote"></textarea>
```

### CKEditor
```blade
<textarea id="content" name="content" class="ck-editor"></textarea>
```

### Contenteditable Elements
```blade
<div id="content" contenteditable="true"></div>
```

## JavaScript API

For advanced usage, you can call the generation directly:

```javascript
// Generate content programmatically
AiGenerator.generate(
    '/admin/ai-assistant/api/generate-post-title',
    '#post_name',
    { context: 'blog post about laravel' }
);

// Update field directly
AiGenerator.updateField('#post_name', 'Generated Title');

// Show notifications
AiGenerator.showSuccess('Content generated successfully!');
AiGenerator.showError('Failed to generate content');
```

## Permissions

All AI generation API endpoints require the `ai-assistant.api.generate` permission.

To ensure users can access these features:
1. Grant role the `ai-assistant.access` permission
2. Users will automatically have access to generation endpoints through role permissions

## Customization

### Button Styling
Edit `resources/css/ai-inline-generator.css`:
```css
.ai-generate-btn {
    transition: all 0.2s ease;
    /* customize appearance */
}

.ai-generate-btn:hover {
    /* customize hover state */
}

.ai-generate-btn.generating {
    /* customize loading state */
}
```

### Button Text
Pass custom text via slot:
```blade
<x-plugins.ai-assistant.inline-button 
    :route="route('admin.ai-assistant.api.generate-post-title')"
    target="#post_name"
>
    ✨ Generate with AI
</x-plugins.ai-assistant.inline-button>
```

### Loading Behavior
The JavaScript automatically:
- Disables the button during generation
- Shows a spinning icon
- Changes button text to "Generating..."
- Re-enables on completion

## Troubleshooting

### Button doesn't appear
- Ensure view includes component: `@include('plugins.ai-assistant::components.inline-button')`
- Check route name is correct in `:route` parameter
- Verify CSS classes are loaded

### AJAX request fails
- Check browser console for error details
- Verify route exists: `php artisan route:list | grep generate`
- Ensure user has `ai-assistant.api.generate` permission
- Check API key is configured in AI Assistant settings

### Content doesn't insert
- Verify target selector matches field ID exactly
- Check if field is a Summernote/CKEditor (different handling)
- Ensure field is not readonly or disabled

### Permissions error
- Check user role has `ai-assistant.access` permission
- Verify admin user is logged in
- Clear browser cache and try again

## Testing Checklist

Before deploying to production:

1. **Button Visibility**
   - [ ] Buttons appear on all content editing pages
   - [ ] Buttons are properly styled and aligned

2. **Generation**
   - [ ] Click button shows loading spinner
   - [ ] Content is generated successfully
   - [ ] Generated content appears in target field
   - [ ] Success notification is shown

3. **Error Handling**
   - [ ] Error message shows if API key not configured
   - [ ] Error message shows if generation fails
   - [ ] Button re-enables after error

4. **Editor Support**
   - [ ] Works with text inputs
   - [ ] Works with textareas
   - [ ] Works with Summernote editors
   - [ ] Works with CKEditor instances

5. **Field Types**
   - [ ] Title generation works
   - [ ] Description generation works
   - [ ] SEO field generation works
   - [ ] Multi-language content works

6. **Performance**
   - [ ] Generation completes in reasonable time (< 30s)
   - [ ] No JavaScript errors in console
   - [ ] Page remains responsive during generation

7. **Permissions**
   - [ ] Regular users can generate content
   - [ ] Permission checks work correctly
   - [ ] Unauthenticated users get proper error

## Next Steps

1. **Integrate buttons into views:**
   - Blog post create/edit
   - Page create/edit
   - Product create/edit (if eCommerce plugin exists)
   - Category management
   - Tag management
   - All SEO field areas

2. **Test thoroughly** with different content types

3. **Monitor generation quality** and adjust custom instructions

4. **Gather user feedback** and refine prompts

5. **Document custom implementations** for your team

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review controller methods for available parameters
3. Check Laravel logs for detailed errors
4. Verify AI provider configuration in settings

---

**Implementation Date**: January 23, 2026
**Version**: 1.0
**Status**: Ready for integration
