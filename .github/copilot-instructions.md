# AI Coding Agent Instructions for this Repository

This project is a Laravel 12 application structured around Botble CMS. Most application logic lives in modular packages under `platform/` (core, plugins, themes). The `app/` folder is minimal; avoid adding business logic there unless truly application-specific.

## Architecture Overview
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