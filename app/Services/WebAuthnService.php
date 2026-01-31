<?php

namespace App\Services;

use App\Models\BiometricCredential;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Simplified WebAuthn Service
 * 
 * Handles WebAuthn operations with minimal dependencies.
 * Uses basic PHP for credential management.
 */
class WebAuthnService
{
    /**
     * Cache TTL for challenges (60 seconds)
     */
    private const CHALLENGE_TTL = 60;

    /**
     * Relying Party ID (domain)
     */
    private string $rpId;

    /**
     * Relying Party Name
     */
    private string $rpName;

    /**
     * Origin URL
     */
    private string $origin;

    public function __construct()
    {
        $appUrl = config('app.url', 'https://authenticator.task19.com');
        $this->rpId = parse_url($appUrl, PHP_URL_HOST) ?: 'authenticator.task19.com';
        $this->rpName = config('app.name', 'Authenticator');
        $this->origin = rtrim($appUrl, '/');
        
        Log::info('WebAuthnService initialized', [
            'rpId' => $this->rpId,
            'rpName' => $this->rpName,
            'origin' => $this->origin
        ]);
    }

    /**
     * Generate registration options for a user
     *
     * @param User $user
     * @return array
     */
    public function generateRegistrationOptions(User $user): array
    {
        try {
            // Generate random challenge
            $challenge = random_bytes(32);
            $challengeB64 = $this->base64UrlEncode($challenge);
            
            // Store challenge in cache with user ID
            $challengeKey = 'webauthn_register_challenge_' . $user->id;
            Cache::put($challengeKey, $challengeB64, self::CHALLENGE_TTL);

            // Get existing credentials to exclude
            $excludeCredentials = $user->biometricCredentials->map(function ($credential) {
                return [
                    'type' => 'public-key',
                    'id' => $credential->credential_id,
                ];
            })->toArray();

            $options = [
                'challenge' => $challengeB64,
                'rp' => [
                    'name' => $this->rpName,
                    'id' => $this->rpId,
                ],
                'user' => [
                    'id' => $this->base64UrlEncode((string) $user->id),
                    'name' => $user->email,
                    'displayName' => $user->name,
                ],
                'pubKeyCredParams' => [
                    ['type' => 'public-key', 'alg' => -7],  // ES256
                    ['type' => 'public-key', 'alg' => -257], // RS256
                ],
                'timeout' => 60000,
                'excludeCredentials' => $excludeCredentials,
                'authenticatorSelection' => [
                    'authenticatorAttachment' => 'platform',
                    'userVerification' => 'required',
                    'requireResidentKey' => false,
                ],
                'attestation' => 'none',
            ];

            Log::info('Registration options generated', [
                'user_id' => $user->id,
                'challenge_length' => strlen($challengeB64)
            ]);

            return $options;
            
        } catch (\Exception $e) {
            Log::error('Error in generateRegistrationOptions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verify registration response and store credential
     *
     * @param User $user
     * @param array $response
     * @return BiometricCredential
     * @throws \Exception
     */
    public function verifyRegistration(User $user, array $response): BiometricCredential
    {
        try {
            // Retrieve stored challenge
            $challengeKey = 'webauthn_register_challenge_' . $user->id;
            $storedChallenge = Cache::get($challengeKey);
            
            if (!$storedChallenge) {
                throw new \Exception('Challenge not found or expired');
            }

            // Clear the challenge
            Cache::forget($challengeKey);

            // Extract credential data
            $credentialId = $response['id'] ?? null;
            $rawId = $response['rawId'] ?? null;
            $clientDataJSON = $response['response']['clientDataJSON'] ?? null;
            $attestationObject = $response['response']['attestationObject'] ?? null;

            if (!$credentialId || !$rawId || !$clientDataJSON || !$attestationObject) {
                throw new \Exception('Invalid registration response - missing required fields');
            }

            // Decode and verify client data
            $clientDataDecoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $clientDataJSON));
            $clientData = json_decode($clientDataDecoded, true);
            
            if (!$clientData) {
                throw new \Exception('Invalid client data JSON');
            }

            // Verify challenge
            if (!isset($clientData['challenge']) || $clientData['challenge'] !== $storedChallenge) {
                throw new \Exception('Challenge mismatch');
            }

            // Verify type
            if (!isset($clientData['type']) || $clientData['type'] !== 'webauthn.create') {
                throw new \Exception('Invalid ceremony type');
            }

            // Verify origin
            if (!isset($clientData['origin']) || !$this->verifyOrigin($clientData['origin'])) {
                throw new \Exception('Origin mismatch: expected ' . $this->origin . ', got ' . ($clientData['origin'] ?? 'none'));
            }

            // Create credential record (simplified - not verifying attestation)
            $credential = BiometricCredential::create([
                'user_id' => $user->id,
                'credential_id' => $credentialId,
                'public_key' => json_encode([
                    'clientDataJSON' => $clientDataJSON,
                    'attestationObject' => $attestationObject,
                ]),
                'counter' => 0,
                'device_name' => 'Biometric Device',
                'aaguid' => null,
            ]);

            Log::info('Credential registered successfully', [
                'user_id' => $user->id,
                'credential_id' => $credentialId
            ]);

            return $credential;
            
        } catch (\Exception $e) {
            Log::error('Error in verifyRegistration', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Generate authentication options
     *
     * @param string|null $email
     * @return array
     */
    public function generateAuthenticationOptions(?string $email = null): array
    {
        // Generate random challenge
        $challenge = random_bytes(32);
        $challengeB64 = $this->base64UrlEncode($challenge);
        
        // Store challenge in cache
        $challengeKey = 'webauthn_auth_challenge_' . md5($challengeB64);
        Cache::put($challengeKey, $challengeB64, self::CHALLENGE_TTL);

        // Get allowed credentials if email provided
        $allowCredentials = [];
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $allowCredentials = $user->biometricCredentials->map(function ($credential) {
                    return [
                        'type' => 'public-key',
                        'id' => $credential->credential_id,
                    ];
                })->toArray();
            }
        }

        return [
            'challenge' => $challengeB64,
            'timeout' => 60000,
            'rpId' => $this->rpId,
            'allowCredentials' => $allowCredentials,
            'userVerification' => 'required',
        ];
    }

    /**
     * Verify authentication response
     *
     * @param array $response
     * @return User
     * @throws \Exception
     */
    public function verifyAuthentication(array $response): User
    {
        $credentialId = $response['id'] ?? null;
        $clientDataJSON = $response['response']['clientDataJSON'] ?? null;
        $authenticatorData = $response['response']['authenticatorData'] ?? null;
        $signature = $response['response']['signature'] ?? null;

        if (!$credentialId || !$clientDataJSON || !$authenticatorData || !$signature) {
            throw new \Exception('Invalid authentication response');
        }

        // Find credential
        $credential = BiometricCredential::where('credential_id', $credentialId)->first();
        
        if (!$credential) {
            throw new \Exception('Credential not found');
        }

        // Decode client data
        $clientDataDecoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $clientDataJSON));
        $clientData = json_decode($clientDataDecoded, true);
        
        // Verify challenge
        $challengeKey = 'webauthn_auth_challenge_' . md5($clientData['challenge']);
        $storedChallenge = Cache::get($challengeKey);
        
        if (!$storedChallenge || $clientData['challenge'] !== $storedChallenge) {
            throw new \Exception('Challenge mismatch or expired');
        }

        // Clear the challenge
        Cache::forget($challengeKey);

        // Verify origin
        if (!isset($clientData['origin']) || !$this->verifyOrigin($clientData['origin'])) {
            throw new \Exception('Origin mismatch');
        }

        // Verify type
        if (!isset($clientData['type']) || $clientData['type'] !== 'webauthn.get') {
            throw new \Exception('Invalid ceremony type');
        }

        // Update counter (simplified - not verifying signature)
        $credential->increment('counter');

        return $credential->user;
    }

    /**
     * Verify origin matches expected origin
     *
     * @param string $origin
     * @return bool
     */
    private function verifyOrigin(string $origin): bool
    {
        $expectedOrigin = rtrim($this->origin, '/');
        $providedOrigin = rtrim($origin, '/');
        
        return $expectedOrigin === $providedOrigin;
    }

    /**
     * Base64 URL encode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Delete a credential
     *
     * @param int $credentialId
     * @param int $userId
     * @return bool
     */
    public function deleteCredential(int $credentialId, int $userId): bool
    {
        $credential = BiometricCredential::where('id', $credentialId)
            ->where('user_id', $userId)
            ->first();

        if (!$credential) {
            return false;
        }

        return $credential->delete();
    }
}
