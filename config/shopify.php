<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shopify App Credentials
    |--------------------------------------------------------------------------
    |
    | These are your Shopify app credentials from the Partners dashboard
    |
    */

    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'scopes' => env('SHOPIFY_APP_SCOPES', 'read_customers,read_orders,read_products'),
    'app_url' => env('SHOPIFY_APP_URL'),
    'app_name' => env('SHOPIFY_APP_NAME', 'Authenticator'),

    /*
    |--------------------------------------------------------------------------
    | Shop Configuration
    |--------------------------------------------------------------------------
    |
    | Your Shopify store domain
    |
    */

    'shop_domain' => env('SHOPIFY_SHOP_DOMAIN', 'test-store-t100000000000000000000003757.myshopify.com'),

    /*
    |--------------------------------------------------------------------------
    | Storefront API
    |--------------------------------------------------------------------------
    |
    | Storefront API access token for customer authentication
    |
    */

    'storefront_access_token' => env('SHOPIFY_STOREFRONT_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Shopify API version to use
    |
    */

    'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),

];
