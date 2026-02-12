<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BiometricEnrollmentController extends Controller
{
    /**
     * Show biometric enrollment page
     */
    public function showEnrollment(Request $request)
    {
        // Validate required parameters
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|string',
            'email' => 'required|email',
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect('/')->with('error', 'Invalid enrollment parameters');
        }

        // Validate return URL is from allowed domain
        if (!$this->isValidReturnUrl($request->return_url)) {
            Log::warning('Invalid return URL attempted', [
                'return_url' => $request->return_url,
                'ip' => $request->ip()
            ]);
            return redirect('/')->with('error', 'Invalid return URL');
        }

        // Sync Shopify customer to database
        $user = $this->syncShopifyCustomer(
            $request->customer_id,
            $request->email,
            $request->input('first_name'),
            $request->input('last_name')
        );

        if (!$user) {
            return redirect($request->return_url)->with('error', 'Failed to sync customer');
        }

        // Log the user in
        auth()->login($user);

        // Pass data to view
        return view('biometric.enroll', [
            'user' => $user,
            'returnUrl' => $request->return_url,
            'shopifyCustomerId' => $request->customer_id,
        ]);
    }

    /**
     * Show biometric authentication page
     */
    public function showAuth(Request $request)
    {
        // Validate return URL
        $validator = Validator::make($request->all(), [
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect('/')->with('error', 'Invalid authentication parameters');
        }

        // Validate return URL is from allowed domain
        if (!$this->isValidReturnUrl($request->return_url)) {
            Log::warning('Invalid return URL attempted', [
                'return_url' => $request->return_url,
                'ip' => $request->ip()
            ]);
            return redirect('/')->with('error', 'Invalid return URL');
        }

        return view('biometric.auth', [
            'returnUrl' => $request->return_url,
        ]);
    }

    /**
     * Handle successful biometric login and create Shopify session
     */
    public function handleAuthSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'return_url' => 'required|url',
            'shopify_customer_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters'
            ], 422);
        }

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        // Validate return URL
        if (!$this->isValidReturnUrl($request->return_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid return URL'
            ], 400);
        }

        $user = auth()->user();

        Log::info('Biometric auth successful, redirecting to Shopify', [
            'user_id' => $user->id,
            'return_url' => $request->return_url
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => $request->return_url,
            'message' => 'Authentication successful'
        ]);
    }

    /**
     * Validate that return URL is from an allowed domain
     */
    private function isValidReturnUrl(string $url): bool
    {
        $allowedDomains = [
            'myshopify.com',
            config('app.shopify_domain', ''),
        ];

        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        // Must be HTTPS
        if (($parsed['scheme'] ?? '') !== 'https') {
            return false;
        }

        foreach ($allowedDomains as $domain) {
            if (empty($domain)) continue;
            
            if (str_ends_with($host, $domain) || $host === $domain) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show Shopify login bridge page
     */
    public function showShopifyLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect('/')->with('error', 'Invalid parameters');
        }

        // Validate return URL
        if (!$this->isValidReturnUrl($request->return_url)) {
            return redirect('/')->with('error', 'Invalid return URL');
        }

        // Extract shop domain from return URL
        $parsed = parse_url($request->return_url);
        $shopDomain = $parsed['host'] ?? '';
        
        // Redirect to account page with login_hint parameter
        // Shopify will redirect to login if not authenticated, and login_hint will pre-fill the email
        $accountUrl = 'https://' . $shopDomain . '/account?login_hint=' . urlencode($request->email);

        return view('biometric.shopify-login', [
            'email' => $request->email,
            'shopifyDomain' => $shopDomain,
            'returnUrl' => $accountUrl,
        ]);
    }

    /**
     * Sync Shopify customer to local database
     */
    private function syncShopifyCustomer(
        string $shopifyCustomerId,
        string $email,
        ?string $firstName = null,
        ?string $lastName = null
    ): ?User {
        try {
            // Find or create user
            $user = User::where('shopify_customer_id', $shopifyCustomerId)
                       ->orWhere('email', $email)
                       ->first();

            if ($user) {
                // Update existing user
                $user->shopify_customer_id = $shopifyCustomerId;
                if ($firstName || $lastName) {
                    $user->name = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
                }
                $user->save();
            } else {
                // Create new user
                $user = User::create([
                    'name' => trim(($firstName ?? '') . ' ' . ($lastName ?? '')),
                    'email' => $email,
                    'shopify_customer_id' => $shopifyCustomerId,
                    'password' => bcrypt(uniqid()), // Random password
                ]);
            }

            Log::info('Shopify customer synced', [
                'user_id' => $user->id,
                'shopify_customer_id' => $shopifyCustomerId
            ]);

            return $user;

        } catch (\Exception $e) {
            Log::error('Failed to sync Shopify customer', [
                'error' => $e->getMessage(),
                'shopify_customer_id' => $shopifyCustomerId,
                'email' => $email
            ]);
            return null;
        }
    }
}
