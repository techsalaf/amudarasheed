<?php

return [
    'enable_plugin_manager' => env('CMS_PLUGIN_ENABLE_PLUGIN_MANAGER', true),
    'hide_plugin_author' => env('CMS_PLUGIN_HIDE_AUTHOR', false),
    'enable_plugin_list_cache' => env('CMS_PLUGIN_ENABLE_PLUGIN_LIST_CACHE', false),
    'enable_marketplace_feature' => env('CMS_ENABLE_MARKETPLACE_FEATURE', true),
    'marketplace_url' => env('CMS_MARKETPLACE_URL'),
    'marketplace_token' => env('CMS_MARKETPLACE_TOKEN'),
    'marketplace_product_id' => env('CMS_MARKETPLACE_PRODUCT_ID'),
    'marketplace_license_url' => env('CMS_MARKETPLACE_LICENSE_URL'),
    'marketplace_license_api_key' => env('CMS_MARKETPLACE_LICENSE_API_KEY'),
];
