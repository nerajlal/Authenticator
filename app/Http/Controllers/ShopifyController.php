<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BiometricCredential;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopifyController extends Controller
{
    /**
     * Sync Shopify customer with authenticator database
     * Creates or updates user record with Shopify customer ID
     */
    public function syncCustomer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'shopify_customer_id' => 'required|string',
                'email' => 'required|email',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Check if user exists by email or Shopify customer ID
            $user = User::where('email', $data['email'])
                       ->orWhere('shopify_customer_id', $data['shopify_customer_id'])
                       ->first();

            if ($user) {
                // Update existing user
                $user->shopify_customer_id = $data['shopify_customer_id'];
                if (isset($data['first_name'])) $user->name = $data['first_name'] . ' ' . ($data['last_name'] ?? '');
                $user->save();

                Log::info('Shopify customer synced (updated)', [
                    'user_id' => $user->id,
                    'shopify_customer_id' => $data['shopify_customer_id']
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''),
                    'email' => $data['email'],
                    'shopify_customer_id' => $data['shopify_customer_id'],
                    'password' => Hash::make(uniqid()), // Random password, won't be used
                ]);

                Log::info('Shopify customer synced (created)', [
                    'user_id' => $user->id,
                    'shopify_customer_id' => $data['shopify_customer_id']
                ]);
            }

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'message' => 'Customer synced successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Shopify customer sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync customer'
            ], 500);
        }
    }

    /**
     * Check if Shopify customer has biometric credentials enrolled
     */
    public function checkEnrollment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'shopify_customer_id' => 'required|string',
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Find user by Shopify customer ID or email
            $user = User::where('shopify_customer_id', $data['shopify_customer_id'])
                       ->orWhere('email', $data['email'])
                       ->first();

            if (!$user) {
                // User doesn't exist, auto-sync
                $syncResult = $this->syncCustomer($request);
                $syncData = $syncResult->getData(true);
                
                if (!$syncData['success']) {
                    return response()->json([
                        'success' => false,
                        'enrolled' => false,
                        'message' => 'User not found and sync failed'
                    ]);
                }

                $user = User::find($syncData['user_id']);
            }

            // Check if user has biometric credentials
            $hasCredentials = BiometricCredential::where('user_id', $user->id)->exists();

            return response()->json([
                'success' => true,
                'enrolled' => $hasCredentials,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Enrollment check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check enrollment'
            ], 500);
        }
    }

    /**
     * Get user session for Shopify customer
     * Used to authenticate user for biometric enrollment
     */
    public function getSession(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'shopify_customer_id' => 'required|string',
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed'
                ], 422);
            }

            $data = $validator->validated();

            // Find or create user
            $user = User::where('shopify_customer_id', $data['shopify_customer_id'])
                       ->orWhere('email', $data['email'])
                       ->first();

            if (!$user) {
                // Auto-sync customer
                $syncResult = $this->syncCustomer($request);
                $syncData = $syncResult->getData(true);
                
                if (!$syncData['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }

                $user = User::find($syncData['user_id']);
            }

            // Log the user in
            auth()->login($user);

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'authenticated' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Get session failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get session'
            ], 500);
        }
    }
}
