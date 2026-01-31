<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowShopifyEmbedding
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Allow Shopify to embed the app in iframe
        $response->headers->set('X-Frame-Options', 'ALLOW-FROM https://admin.shopify.com');
        $response->headers->set('Content-Security-Policy', "frame-ancestors https://admin.shopify.com https://*.myshopify.com");
        
        return $response;
    }
}