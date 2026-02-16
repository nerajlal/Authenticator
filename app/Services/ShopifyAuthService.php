<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyAuthService
{
    private string $shopDomain;
    private string $storefrontAccessToken;

    public function __construct()
    {
        $this->shopDomain = config('shopify.shop_domain');
        $this->storefrontAccessToken = config('shopify.storefront_access_token');
    }

    /**
     * Authenticate customer with Shopify Storefront API
     */
    public function authenticateCustomer(string $email, string $password): ?array
    {
        try {
            $mutation = <<<'GRAPHQL'
            mutation customerAccessTokenCreate($input: CustomerAccessTokenCreateInput!) {
              customerAccessTokenCreate(input: $input) {
                customerAccessToken {
                  accessToken
                  expiresAt
                }
                customerUserErrors {
                  code
                  field
                  message
                }
              }
            }
            GRAPHQL;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Shopify-Storefront-Access-Token' => $this->storefrontAccessToken,
            ])->post("https://{$this->shopDomain}/api/2024-01/graphql.json", [
                'query' => $mutation,
                'variables' => [
                    'input' => [
                        'email' => $email,
                        'password' => $password,
                    ],
                ],
            ]);

            $data = $response->json();

            if (isset($data['data']['customerAccessTokenCreate']['customerUserErrors']) 
                && count($data['data']['customerAccessTokenCreate']['customerUserErrors']) > 0) {
                Log::error('Shopify authentication failed', [
                    'errors' => $data['data']['customerAccessTokenCreate']['customerUserErrors']
                ]);
                return null;
            }

            if (isset($data['data']['customerAccessTokenCreate']['customerAccessToken'])) {
                return $data['data']['customerAccessTokenCreate']['customerAccessToken'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Shopify authentication error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create Shopify login URL with access token
     */
    public function createLoginUrl(string $accessToken, string $returnUrl = '/account'): string
    {
        return "https://{$this->shopDomain}/account/login/multipass/{$accessToken}?return_url={$returnUrl}";
    }
}
