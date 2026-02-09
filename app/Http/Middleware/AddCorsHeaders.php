<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add CORS headers to allow cross-origin requests
 * Specifically for Shopify integration
 */
class AddCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get the origin from the request
        $origin = $request->header('Origin');
        
        // Allow specific origins (Shopify store and any subdomain)
        $allowedOrigins = [
            'https://test-store-1100000000000000000000000000000003757.myshopify.com',
            'https://shopify.com',
        ];
        
        // Check if origin is allowed
        $allowOrigin = '*';
        if ($origin && (in_array($origin, $allowedOrigins) || str_ends_with($origin, '.myshopify.com'))) {
            $allowOrigin = $origin;
        }

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
