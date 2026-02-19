const mix = require('laravel-mix');

mix.js(
    'platform/plugins/ai-assistant/resources/js/ai-assistant.js',
    'public/vendor/core/plugins/ai-assistant/js'
);

mix.js(
    'platform/plugins/ai-assistant/resources/js/ai-inline-generator.js',
    'public/vendor/core/plugins/ai-assistant/js'
);

mix.js(
    'platform/plugins/ai-assistant/resources/js/ai-provider-models.js',
    'public/vendor/core/plugins/ai-assistant/js'
);

mix.css(
    'platform/plugins/ai-assistant/resources/css/ai-inline-generator.css',
    'public/vendor/core/plugins/ai-assistant/css'
);
