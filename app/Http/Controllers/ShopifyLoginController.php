<?php

namespace App\Http\Controllers;

use App\Services\ShopifyAuthService;
use App\Services\PasswordEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopifyLoginController extends Controller
{
    private ShopifyAuthService $shopifyAuth;
    private PasswordEncryptionService $encryption;

    public function __construct(
        ShopifyAuthService $shopifyAuth,
        PasswordEncryptionService $encryption
    ) {
        $this->shopifyAuth = $shopifyAuth;
        $this->encryption = $encryption;
    }

    /**
     * Show custom login page with pre-filled credentials
     */
    public function showCustomLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect('/')->with('error', 'Invalid login parameters');
        }

        return view('biometric.custom-login', [
            'email' => $request->email,
            'password' => $request->password,
            'returnUrl' => $request->return_url,
        ]);
    }

    /**
     * Authenticate with Shopify and create session
     */
    public function authenticate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request data'
                ], 422);
            }

            $email = $request->input('email');
            $password = $request->input('password');

            // Authenticate with Shopify Storefront API
            $accessToken = $this->shopifyAuth->authenticateCustomer($email, $password);

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            Log::info('Shopify authentication successful', [
                'email' => $email
            ]);

            // Create redirect URL to Shopify with access token
            $shopDomain = config('shopify.shop_domain');
            $redirectUrl = "https://{$shopDomain}/account";

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect_url' => $redirectUrl,
                'access_token' => $accessToken['accessToken']
            ]);

        } catch (\Exception $e) {
            Log::error('Shopify authentication error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
