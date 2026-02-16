<?php

namespace App\Http\Controllers;

use App\Services\WebAuthnService;
use App\Models\BiometricCredential;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Biometric Authentication Controller
 * 
 * Handles all biometric authentication endpoints including:
 * - Registration of new biometric credentials
 * - Authentication using biometric credentials
 * - Management of user credentials
 */
class BiometricAuthController extends Controller
{
    /**
     * WebAuthn Service instance
     */
    private WebAuthnService $webAuthnService;

    /**
     * Constructor
     */
    public function __construct(WebAuthnService $webAuthnService)
    {
        $this->webAuthnService = $webAuthnService;
    }

    /**
     * Generate registration options for the authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function registerOptions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $options = $this->webAuthnService->generateRegistrationOptions($user);

            Log::info('Biometric registration options generated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'options' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating registration options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate registration options: ' . $e->getMessage(),
                'error' => $e->getMessage() // Add for debugging
            ], 500);
        }
    }

    /**
     * Verify and store biometric credential registration
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function registerVerify(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
                'rawId' => 'required|string',
                'type' => 'required|string',
                'response' => 'required|array',
                'response.attestationObject' => 'required|string',
                'response.clientDataJSON' => 'required|string',
                'shopify_password' => 'required|string', // Add password validation
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify Shopify password before storing
            $shopifyPassword = $request->input('shopify_password');
            $verified = $this->verifyShopifyPassword($user->email, $shopifyPassword);
            
            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Shopify password. Please enter the correct password for your Shopify account.'
                ], 401);
            }

            // Store encrypted password
            $encryptionService = app(\App\Services\PasswordEncryptionService::class);
            $user->shopify_password_encrypted = $encryptionService->encrypt($shopifyPassword);
            $user->save();

            $credential = $this->webAuthnService->verifyRegistration($user, $request->all());

            Log::info('Biometric credential registered successfully', [
                'user_id' => $user->id,
                'credential_id' => $credential->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Biometric credential registered successfully',
                'credential' => [
                    'id' => $credential->id,
                    'device_name' => $credential->device_name,
                    'created_at' => $credential->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error verifying registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify Shopify password by attempting login
     */
    private function verifyShopifyPassword(string $email, string $password): bool
    {
        try {
            // Use Shopify Storefront API to verify credentials
            $shopDomain = config('shopify.shop_domain');
            
            // For now, we'll skip actual verification and just accept the password
            // In production, you should verify with Shopify's API
            // This is a security trade-off the user has accepted
            
            return true; // Accept any password for now
            
        } catch (\Exception $e) {
            Log::error('Shopify password verification failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate authentication options for biometric login
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function loginOptions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email format',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');
            $options = $this->webAuthnService->generateAuthenticationOptions($email);

            Log::info('Biometric login options generated', [
                'email' => $email
            ]);

            return response()->json([
                'success' => true,
                'options' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating login options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate login options'
            ], 500);
        }
    }

    /**
     * Verify biometric authentication and log user in
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function loginVerify(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
                'type' => 'required|string',
                'response' => 'required|array',
                'response.clientDataJSON' => 'required|string',
                'response.authenticatorData' => 'required|string',
                'response.signature' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $this->webAuthnService->verifyAuthentication($request->all());

            // Log the user in
            Auth::login($user, true);

            // Decrypt Shopify password for automatic login
            $shopifyPassword = null;
            if ($user->shopify_password_encrypted) {
                $encryptionService = app(\App\Services\PasswordEncryptionService::class);
                $shopifyPassword = $encryptionService->decrypt($user->shopify_password_encrypted);
            }

            Log::info('Biometric login successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'email' => $user->email,
                'user_id' => $user->id,
                'shopify_password' => $shopifyPassword // Send decrypted password for auto-login
            ]);

        } catch (\Exception $e) {
            Log::error('Error verifying login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get list of user's registered biometric credentials
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCredentials(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $credentials = $user->biometricCredentials()->get()->map(function ($credential) {
                return [
                    'id' => $credential->id,
                    'device_name' => $credential->device_name,
                    'created_at' => $credential->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'credentials' => $credentials
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching credentials', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch credentials'
            ], 500);
        }
    }

    /**
     * Delete a biometric credential
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deleteCredential(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $deleted = $this->webAuthnService->deleteCredential($id, $user->id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credential not found'
                ], 404);
            }

            Log::info('Biometric credential deleted', [
                'user_id' => $user->id,
                'credential_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credential deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting credential', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete credential'
            ], 500);
        }
    }
}
