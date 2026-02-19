# AI Coding Agent Instructions for this Repository

This project is a Laravel 12 application structured around Botble CMS. Most application logic lives in modular packages under `platform/` (core, plugins, themes). The `app/` folder is minimal; avoid adding business logic there unless truly application-specific.

## Table of Contents
- [Why Use an AI Assistant Guide?](#why-use-an-ai-assistant-guide)
- [Project Architecture Overview](#project-architecture-overview)
- [Tech Stack](#tech-stack)
- [Naming Conventions](#naming-conventions)
- [Critical: Botble Enum Usage](#critical-botble-enum-usage)
- [Database Conventions](#database-conventions)
- [Eloquent Best Practices](#eloquent-best-practices)
- [Translation System](#translation-system)
- [Form Builder](#form-builder)
- [Hooks System](#hooks-system)
- [Security Best Practices](#security-best-practices)
- [Code Quality Commands](#code-quality-commands)
- [Setting Up Your AI Assistant](#setting-up-your-ai-assistant)
- [Conclusion](#conclusion)

---

## Why Use an AI Assistant Guide?
When working with AI code assistants, providing context about my project's architecture, conventions, and patterns helps generate code that:
- Follows my existing coding standards
- Uses the correct design patterns
- Integrates seamlessly with my codebase
- Avoids common pitfalls and anti-patterns

## Project Architecture Overview
Botble CMS uses a modular Laravel structure organized in the `/platform` directory:

| Directory | Purpose |
|-----------|---------|
| `core/` | Foundation modules (ACL, base, dashboard, media, settings, table) |
| `packages/` | Reusable packages (menu, page, SEO, theme, widget) |
| `plugins/` | Feature plugins (blog, ecommerce, contact, gallery) |
| `themes/` | Frontend presentation layer |

## Tech Stack
- **Backend**: Laravel 12+, PHP 8.2+
- **Frontend**: Vue.js 3, Bootstrap 5, jQuery
- **Build Tools**: Laravel Mix, npm workspaces
- **Database**: MySQL (SQLite for tests)
- **UI Framework**: Tabler UI

- **Modules**: Code is organized as:
  - `platform/core/*`: Shared framework features (routing, menus, settings, base services).
  - `platform/plugins/*`: Feature modules (e.g., `blog`, `contact`, `faq`).
  - `platform/themes/*`: Frontend themes (e.g., `zelio`) with routes, assets, and views.
- **Composer merge**: `wikimedia/composer-merge-plugin` pulls `composer.json` from plugins/themes into the app (see `composer.json -> extra.merge-plugin`).
- **Service providers**: Core `BaseServiceProvider` auto-loads configs, views, translations, routes, migrations, and assets. Plugins define providers in `platform/plugins/<name>/src/Providers/*`.
- **Routing**:
  - Admin routes register via `AdminHelper::registerRoutes(...)` inside plugin `routes/web.php`.
  - Public site routes register via `Theme::registerRoutes(...)` and `Theme::routes()` inside theme or plugin.
  - Root `routes/web.php` is intentionally empty; add routes within modules.
- **Views**: Plugin views live in `platform/plugins/<name>/resources/views`. Theme views live in `platform/themes/<name>/views` and related folders (`layouts`, `partials`, `widgets`).

## Naming Conventions
Consistent naming is crucial for maintainability. Here are the conventions used in Botble CMS:

| Type | Convention | Example |
|------|------------|---------|
| **Files** | kebab-case | `product-controller.php` |
| **Classes/Enums** | PascalCase | `ProductController` |
| **Methods** | camelCase | `getProductList()` |
| **Variables** | snake_case | `$product_name` |
| **Constants** | SCREAMING_SNAKE_CASE | `PUBLISHED` |
| **Database Tables** | snake_case plural | `products` |
| **Eloquent Models** | Singular PascalCase | `Product` |
| **Routes** | kebab-case | `/product-list` |

## Developer Workflows
- **Install & bootstrap**:
  - `composer install`
  - `php artisan key:generate` (auto-run on create via composer script)
  - `php artisan cms:publish:assets` (auto-run post-update; publishes module assets)
  - Ensure web server DocumentRoot points to `public/` (XAMPP/Apache).
- **Database & migrations**:
  - Migrations live in core and plugins; they auto-load via providers. Run: `php artisan migrate`.
  - Test env uses in-memory SQLite (see `phpunit.xml`).
- **Build assets (laravel-mix)**:
  - Root `webpack.mix.js` discovers module mix files. Build all: `npm run dev` or `npm run prod`.
  - Build specific module by setting env variables (see `webpack.mix.js`):
    - Theme: `npm_config_theme=<themeName>`
    - Plugin: `npm_config_plugin=<pluginName>`
    - Package/Core similarly.
  - Example: `npm run dev` with a theme: `npm_config_theme=zelio npm run dev`.
- **Testing**:
  - `vendor/bin/phpunit` or `php artisan test` (uses `phpunit.xml`).

## Conventions & Patterns
- **Routes & permissions**: Admin routes define `permission` keys and use `Route::resource` patterns. Example from `platform/plugins/blog/routes/web.php`:
  ```php
  AdminHelper::registerRoutes(function () {
      Route::group(['prefix' => 'blog'], function () {
          Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
              Route::resource('', 'PostController')->parameters(['' => 'post']);
          });
      });
  });
  ```
- **Theme routes**: Theme modules register public routes via `Theme::registerRoutes(...)` and `Theme::routes()` (see `platform/themes/zelio/routes/web.php`).
- **Config & translations**: Each module ships `config/` and `resources/lang/`; core loads and publishes them via `LoadAndPublishDataTrait`.
- **Views**: Plugin views under `platform/plugins/<name>/resources/views`; theme views under `platform/themes/<name>/views` and related folders (`layouts`, `partials`, `widgets`).
- **Assets**: Each module has its own `webpack.mix.js` and `public/` output; root mix aggregates based on env filters.
- **Cleanup on uninstall**: Plugins implement `Plugin::remove()` to drop tables, delete menu nodes/widgets/settings.

## Integration Points
- **Admin UI**: Menus via `DashboardMenu` (see core `BaseServiceProvider::registerDashboardMenus`).
- **Settings**: Use `Botble\Setting` to store/retrieve settings; plugins often provide `/settings/<plugin>` routes.
- **Global search, breadcrumbs, panels**: Provided by core base services (`platform/core/base/src`). Prefer using facades over custom implementations.

## Practical Tips
- Add features inside a plugin: create routes in `routes/web.php`, controllers under `src/Http/Controllers`, models in `src/Models`, and views in `resources/views`.
- Prefer module boundaries: don’t place feature code in `app/` when it belongs to a plugin or theme.
- Avoid `route:cache` in highly dynamic setups; routes are registered via module providers and helpers.
- Keep permissions consistent with admin route groups; ensure matching policies and menu visibility.

If any of these areas are unclear (e.g., plugin activation flow, specific build targets, or environment specifics), please comment and I’ll refine this guide with concrete examples from your modules.

## Critical: Botble Enum Usage
One of the most important things AI assistants need to understand is that **Botble uses a custom Enum class**, NOT PHP 8.1 native enums.

### Common Mistake
```php
// WRONG - This will throw an error!
$model->status->value
// "Cannot access protected property"
```

### Correct Approaches
```php
// Using getValue() method
$status = $model->status->getValue();

// String casting
$status = (string) $model->status;

// Using equals() for comparisons
if ($model->status->equals(BaseStatusEnum::PUBLISHED())) {
    // ...
}

// Using static method for enum instance
$enum = BaseStatusEnum::PUBLISHED();
```

## Database Conventions
### Standard Model Fields
Most models include these common fields:
- `id` - Primary key (supports both integer and UUID)
- `name` or `title` - Display name
- `slug` - URL-friendly identifier
- `status` - Uses `BaseStatusEnum`
- `created_at`, `updated_at` - Timestamps
- `author_id`, `author_type` - Polymorphic author relationship

### ID Type Support
Botble CMS supports both integer IDs and UUIDs. Always use union types:
```php
// Correct - supports both ID types
public function show(int|string $id): Response

// Incorrect - only supports integers
public function show(int $id): Response
```

## Eloquent Best Practices
### Always Use `query()` Method
```php
// Correct
User::query()->where('status', 'published')->get();

// Avoid
User::where('status', 'published')->get();
```

### Prevent N+1 Queries
```php
// Correct - eager loading
$posts = Post::query()
    ->with(['categories', 'tags', 'author'])
    ->get();

// Avoid - causes N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Extra query each iteration!
}
```

## Translation System
Botble CMS uses different translation functions depending on context:

- **Admin Panel**: `trans('plugins/blog::posts.create')` or `trans('core/base::forms.save')`
- **Frontend/Theme**: `__('Read more')` or `__('theme.welcome_message')`

### Translation File Location
`platform/{core|packages|plugins}/*/resources/lang/{locale}/*.php`

### Best Practices
- Never convert string translations to arrays
- Always escape apostrophes in single-quoted strings
- Use flat keys instead of nested arrays

## Form Builder
Botble CMS provides a powerful form builder with `FieldOptions`:

```php
$this
    ->add('name', TextField::class, NameFieldOption::make()->required())
    ->add('status', SelectField::class, StatusFieldOption::make())
    ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
    ->add('image', MediaImageField::class, MediaImageFieldOption::make()->label('Featured Image'));
```

### Available Field Types
- `TextField`, `NumberField`, `HiddenField`
- `SelectField`, `RadioField`, `CheckboxField`
- `EditorField`, `TextareaField`
- `MediaImageField`, `MediaImagesField`, `FileField`
- `DatePickerField`, `TimePickerField`, `ColorField`
- `RepeaterField`, `TagField`, `TreeCategoryField`

## Hooks System
Botble CMS uses WordPress-style hooks for extensibility.

### Actions
Execute code at specific points:
```php
// Register an action
add_action('BASE_ACTION_AFTER_CREATE_CONTENT', function ($screen, $request, $model) {
    // Do something after content is created
}, 20);

// Trigger an action
do_action('BASE_ACTION_AFTER_CREATE_CONTENT', SCREEN_NAME, $request, $model);
```

### Filters
Modify values before they're used:
```php
// Register a filter
add_filter('BASE_FILTER_BEFORE_RENDER_FORM', function ($form, $data) {
    // Modify form before rendering
    return $form;
}, 20);

// Apply a filter
$form = apply_filters('BASE_FILTER_BEFORE_RENDER_FORM', $form, $data);
```

## Security Best Practices
### XSS Prevention in Blade Templates
```php
// For HTML contexts - use BaseHelper::clean()
{!! BaseHelper::clean($userContent) !!}

// For JavaScript contexts - use @json directive
<script>
var data = @json($variable);
</script>

// Default auto-escaping (safe for most cases)
{{ $variable }}
```

### CSRF in AJAX Requests
```javascript
headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
```

## Code Quality Commands
Before committing code, always run these quality checks:

```bash
# Format PHP code
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse --level=5

# Run tests
php artisan test
```

## Setting Up Your AI Assistant
To configure your AI assistant for Botble CMS development:
1. Create a `CLAUDE.md` file (or equivalent) in your project root.
2. Include architecture information about your specific project.
3. Document custom conventions your team follows.
4. List common patterns used in your codebase.
5. Reference this guide for standard Botble CMS conventions.

## Conclusion
By providing proper context to AI assistants, you can significantly improve code generation quality and reduce the time spent on corrections. Remember to:
- Use the custom Enum class correctly (not PHP 8.1 enums)
- Follow naming conventions consistently
- Use `query()` for Eloquent operations
- Apply proper security practices
- Run quality checks before committing

For more detailed information, visit the [official Botble CMS documentation](https://docs.botble.com).
