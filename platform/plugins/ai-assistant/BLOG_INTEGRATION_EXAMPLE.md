# Blog Post Integration Example

This example shows how to add AI generation buttons to the Blog plugin's post create/edit form.

## File Location
`platform/plugins/blog/resources/views/posts/form.blade.php` (or similar)

## Integration Points

### 1. After Title Field

Find the section where the post title/name is defined:

```blade
<!-- BEFORE (original) -->
<x-core::form.field-input
    :name="'name'"
    :label="trans('core/base::forms.name')"
    :value="$post->name"
    :required="true"
    :wrapper-attributes="['class' => 'mb-3']"
/>
```

Replace with:

```blade
<!-- AFTER (with AI button) -->
<div class="mb-3">
    <x-core::form.field-input
        :name="'name'"
        :label="trans('core/base::forms.name')"
        :value="$post->name"
        :required="true"
        :wrapper-attributes="['class' => 'flex-grow-1']"
    />
    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-post-title')"
            target="input[name='name']"
            field="Title"
            class="btn-sm"
        />
    @endif
</div>
```

### 2. After Description/Excerpt Field

Find the excerpt field:

```blade
<!-- BEFORE -->
<x-core::form.field-textarea
    :name="'description'"
    :label="trans('core/base::forms.description')"
    :value="$post->description"
    :wrapper-attributes="['class' => 'mb-3']"
/>
```

Add after:

```blade
<!-- AFTER -->
<x-core::form.field-textarea
    :name="'description'"
    :label="trans('core/base::forms.description')"
    :value="$post->description"
    :wrapper-attributes="['class' => 'mb-3']"
/>
@if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
    <x-plugins.ai-assistant.inline-button 
        :route="route('admin.ai-assistant.api.generate-post-content')"
        target="textarea[name='content']"
        :inputs="['title' => 'input[name=\"name\"]', 'context' => 'textarea[name=\"description\"]']"
        field="Content"
        class="btn-sm"
    />
@endif
```

### 3. For Content Editor

Find where the main content/editor is defined:

```blade
<!-- BEFORE -->
<x-core::form.field-wysiwyg
    :name="'content'"
    :label="trans('core/base::forms.content')"
    :value="$post->content"
    :wrapper-attributes="['class' => 'mb-3']"
/>
```

This already has the button above, but here's the full example:

```blade
<!-- AFTER -->
<div class="mb-3">
    <x-core::form.field-wysiwyg
        :name="'content'"
        :label="trans('core/base::forms.content')"
        :value="$post->content"
        :wrapper-attributes="['class' => 'flex-grow-1']"
    />
    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-post-content')"
            target="textarea[name='content']"
            :inputs="['title' => 'input[name=\"name\"]', 'context' => 'textarea[name=\"description\"]']"
            field="Content"
            class="btn-sm mt-2"
        />
    @endif
</div>
```

### 4. For SEO Fields

In the SEO section (usually at the bottom):

```blade
<!-- SEO Title -->
<div class="mb-3">
    <label for="seo_title">{{ trans('core/base::forms.seo_title') }}</label>
    <input 
        type="text" 
        id="seo_title" 
        name="seo_title" 
        class="form-control" 
        maxlength="60"
        value="{{ $post->seo_title ?? '' }}"
    >
    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-seo-title')"
            target="#seo_title"
            :inputs="['content_title' => 'input[name=\"name\"]']"
            field="SEO Title"
            class="btn-sm mt-2"
        />
    @endif
    <small class="form-text text-muted">{{ trans('seo.meta_title_note', ['max' => 60]) }}</small>
</div>

<!-- SEO Description -->
<div class="mb-3">
    <label for="seo_description">{{ trans('core/base::forms.seo_description') }}</label>
    <textarea 
        id="seo_description" 
        name="seo_description" 
        class="form-control" 
        rows="2" 
        maxlength="160"
    >{{ $post->seo_description ?? '' }}</textarea>
    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
        <x-plugins.ai-assistant.inline-button 
            :route="route('admin.ai-assistant.api.generate-seo-description')"
            target="#seo_description"
            :inputs="['content_title' => 'input[name=\"name\"]', 'content_snippet' => 'textarea[name=\"content\"]']"
            field="SEO Desc"
            class="btn-sm mt-2"
        />
    @endif
    <small class="form-text text-muted">{{ trans('seo.meta_description_note', ['max' => 160]) }}</small>
</div>
```

## Complete Example Form Structure

Here's a complete example of how the Blog post form should look with all AI buttons integrated:

```blade
@extends('core/base::layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="container-xl">
            <form action="{{ route('blog.posts.store') }}" method="POST" class="form-horizontal">
                @csrf

                <div class="page-body">
                    <!-- Title with AI Button -->
                    <div class="mb-3">
                        <label>{{ trans('core/base::forms.name') }}</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="name" 
                                class="form-control" 
                                value="{{ $post->name ?? '' }}"
                                placeholder="Post title..."
                            >
                            @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
                                <x-plugins.ai-assistant.inline-button 
                                    :route="route('admin.ai-assistant.api.generate-post-title')"
                                    target="input[name='name']"
                                    field="Title"
                                    class="btn-sm"
                                />
                            @endif
                        </div>
                    </div>

                    <!-- Description/Excerpt -->
                    <div class="mb-3">
                        <label>{{ trans('core/base::forms.description') }}</label>
                        <textarea 
                            name="description" 
                            class="form-control" 
                            rows="2"
                            placeholder="Short excerpt..."
                        >{{ $post->description ?? '' }}</textarea>
                    </div>

                    <!-- Main Content with AI Button -->
                    <div class="mb-3">
                        <label>{{ trans('core/base::forms.content') }}</label>
                        <textarea 
                            name="content" 
                            class="form-control editor" 
                            rows="10"
                            placeholder="Post content..."
                        >{{ $post->content ?? '' }}</textarea>
                        @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
                            <x-plugins.ai-assistant.inline-button 
                                :route="route('admin.ai-assistant.api.generate-post-content')"
                                target="textarea[name='content']"
                                :inputs="['title' => 'input[name=\"name\"]', 'context' => 'textarea[name=\"description\"]']"
                                field="Content"
                                class="btn-sm mt-2"
                            />
                        @endif
                    </div>

                    <!-- SEO Section -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>{{ trans('seo.seo') }}</h3>
                        </div>
                        <div class="card-body">
                            <!-- SEO Title -->
                            <div class="mb-3">
                                <label for="seo_title">{{ trans('core/base::forms.seo_title') }}</label>
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        id="seo_title" 
                                        name="seo_title" 
                                        class="form-control" 
                                        maxlength="60"
                                        value="{{ $post->seo_title ?? '' }}"
                                    >
                                    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
                                        <x-plugins.ai-assistant.inline-button 
                                            :route="route('admin.ai-assistant.api.generate-seo-title')"
                                            target="#seo_title"
                                            :inputs="['content_title' => 'input[name=\"name\"]']"
                                            field="Title"
                                            class="btn-sm"
                                        />
                                    @endif
                                </div>
                                <small class="form-text text-muted">50-60 characters</small>
                            </div>

                            <!-- SEO Description -->
                            <div class="mb-3">
                                <label for="seo_description">{{ trans('core/base::forms.seo_description') }}</label>
                                <div class="d-flex gap-2">
                                    <textarea 
                                        id="seo_description" 
                                        name="seo_description" 
                                        class="form-control" 
                                        rows="2" 
                                        maxlength="160"
                                        placeholder="Meta description..."
                                    >{{ $post->seo_description ?? '' }}</textarea>
                                    @if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))
                                        <x-plugins.ai-assistant.inline-button 
                                            :route="route('admin.ai-assistant.api.generate-seo-description')"
                                            target="#seo_description"
                                            :inputs="['content_title' => 'input[name=\"name\"]', 'content_snippet' => 'textarea[name=\"content\"]']"
                                            field="Desc"
                                            class="btn-sm"
                                            style="height: fit-content;"
                                        />
                                    @endif
                                </div>
                                <small class="form-text text-muted">155-160 characters for search results</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="page-body">
                    <button type="submit" class="btn btn-primary">
                        {{ trans('core/base::forms.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
```

## Important Notes

1. **Plugin Check**: The condition `@if(function_exists('get_active_plugins') && in_array('ai-assistant', get_active_plugins()))` ensures buttons only show if AI Assistant plugin is active.

2. **Field Selectors**: Use specific selectors:
   - `input[name='name']` for inputs
   - `textarea[name='content']` for textareas
   - `#seo_title` for elements with IDs

3. **Class Names**: Use consistent CSS classes for styling:
   - `btn-sm` for small buttons
   - `mt-2` for margin-top if needed

4. **Input Context**: Pass related fields to provide context:
   - When generating post content, pass title and description
   - When generating SEO, pass main content and title

## Testing the Integration

1. Navigate to blog post create/edit page
2. Verify AI buttons appear next to relevant fields
3. Click a button and wait for generation
4. Confirm content populates the target field
5. Check browser console for any JavaScript errors

## Customization Tips

- Change button text: `field="Generate Title"` or use slot content
- Change button size: Use `class="btn-lg"` or `class="btn-sm"`
- Change button position: Move component before/after field
- Add loading text: Component automatically shows "Generating..."
- Custom styling: Edit `resources/css/ai-inline-generator.css`

---

**Remember**: Test thoroughly before deploying to production!
