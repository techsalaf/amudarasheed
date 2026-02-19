<?php

return [
    'enable' => true,
    'url' => env('CMS_MARKETPLACE_URL', 'https://marketplace.botble.com'),
    'token' => env('CMS_MARKETPLACE_TOKEN', 'guest-token'),
    'product_id' => env('CMS_MARKETPLACE_PRODUCT_ID', ''),
    'license_url' => env('CMS_MARKETPLACE_LICENSE_URL', 'https://api.botble.com'),
    'license_api_key' => env('CMS_MARKETPLACE_LICENSE_API_KEY', ''),
];
