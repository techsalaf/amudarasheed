<?php

return [
    'enable_marketplace_feature' => true,
    'marketplace_url' => env('CMS_MARKETPLACE_URL', 'https://marketplace.botble.com'),
    'marketplace_token' => env('CMS_MARKETPLACE_TOKEN', 'guest-token'),
    'marketplace_product_id' => env('CMS_MARKETPLACE_PRODUCT_ID', ''),
    'marketplace_license_url' => env('CMS_MARKETPLACE_LICENSE_URL', 'https://api.botble.com'),
    'marketplace_license_api_key' => env('CMS_MARKETPLACE_LICENSE_API_KEY', ''),
];
