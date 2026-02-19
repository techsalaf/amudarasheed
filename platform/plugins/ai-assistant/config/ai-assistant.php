<?php

return [
    'enable' => env('AI_ASSISTANT_ENABLED', true),
    
    'drivers' => [
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
        ],
        'gemini' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ],
        'claude' => [
            'base_url' => 'https://api.anthropic.com/v1',
        ],
        'deepseek' => [
            'base_url' => 'https://api.deepseek.com/v1',
        ],
        'openrouter' => [
            'base_url' => 'https://openrouter.ai/api/v1',
        ],
        'grok' => [
            'base_url' => 'https://api.x.ai/v1',
        ],
    ],

    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'timeout' => 120,
    ],

    'permissions' => [
        'ai-assistant.access',
        'ai-assistant.generate',
        'ai-assistant.settings',
        'ai-assistant.keys',
        'ai-assistant.instructions',
        'ai-assistant.usage',
    ],
];
