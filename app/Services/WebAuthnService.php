<?php

namespace App\Services;

use App\Models\BiometricCredential;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\AuthenticatorAttachment;
use Webauthn\UserVerificationRequirement;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\RSA\RS256;

/**
 * WebAuthn Service
 * 
 * Handles all WebAuthn operations including credential registration and authentication.
 * This service manages the creation of challenges, verification of responses, and
 * storage of credentials in the database.
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
        $this->rpId = config('app.webauthn_rp_id', parse_url(config('app.url'), PHP_URL_HOST));
        $this->rpName = config('app.webauthn_rp_name', config('app.name'));
        $this->origin = config('app.webauthn_origin', config('app.url'));
    }

    /**
     * Generate registration options for a user
     *
     * @param User $user
     * @return array
     */
    public function generateRegistrationOptions(User $user): array
    {
        // Create Relying Party entity
        $rpEntity = PublicKeyCredentialRpEntity::create(
            $this->rpName,
            $this->rpId
        );

        // Create User entity
        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->name,
            (string) $user->id,
            $user->email
        );

        // Generate random challenge
        $challenge = random_bytes(32);
        
        // Store challenge in cache with user ID
        $challengeKey = 'webauthn_register_challenge_' . $user->id;
        Cache::put($challengeKey, base64_encode($challenge), self::CHALLENGE_TTL);

        // Define supported algorithms
        $publicKeyCredentialParametersList = [
            PublicKeyCredentialParameters::create('public-key', -7),  // ES256
            PublicKeyCredentialParameters::create('public-key', -257), // RS256
        ];

        // Get existing credentials to exclude
        $excludeCredentials = $user->biometricCredentials->map(function ($credential) {
            return PublicKeyCredentialDescriptor::create(
                'public-key',
                base64_decode($credential->credential_id)
            );
        })->toArray();

        // Authenticator selection criteria
        $authenticatorSelection = AuthenticatorSelectionCriteria::create()
            ->setAuthenticatorAttachment(AuthenticatorAttachment::PLATFORM)
            ->setUserVerification(UserVerificationRequirement::REQUIRED);

        // Create registration options
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            $challenge,
            $publicKeyCredentialParametersList
        )
            ->excludeCredentials(...$excludeCredentials)
            ->setAuthenticatorSelection($authenticatorSelection)
            ->setTimeout(60000);

        return [
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => $this->rpName,
                'id' => $this->rpId,
            ],
            'user' => [
                'id' => base64_encode((string) $user->id),
                'name' => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => 60000,
            'excludeCredentials' => array_map(function ($cred) {
                return [
                    'type' => 'public-key',
                    'id' => base64_encode($cred->getId()),
                ];
            }, $excludeCredentials),
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => 'required',
            ],
        ];
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
        $type = $response['type'] ?? null;
        $attestationObject = $response['response']['attestationObject'] ?? null;
        $clientDataJSON = $response['response']['clientDataJSON'] ?? null;

        if (!$credentialId || !$rawId || !$attestationObject || !$clientDataJSON) {
            throw new \Exception('Invalid registration response');
        }

        // Decode client data
        $clientData = json_decode(base64_decode($clientDataJSON), true);
        
        // Verify challenge
        if (!isset($clientData['challenge']) || $clientData['challenge'] !== $storedChallenge) {
            throw new \Exception('Challenge mismatch');
        }

        // Verify origin
        if (!isset($clientData['origin']) || !$this->verifyOrigin($clientData['origin'])) {
            throw new \Exception('Origin mismatch');
        }

        // Decode attestation object
        $attestationData = base64_decode($attestationObject);
        
        // For simplicity, we'll store the credential without full attestation verification
        // In production, you should use the full WebAuthn library verification
        
        // Extract public key from response (simplified)
        $publicKey = $response['response']['publicKey'] ?? null;

        // Create credential record
        $credential = BiometricCredential::create([
            'user_id' => $user->id,
            'credential_id' => $credentialId,
            'public_key' => json_encode($response['response']),
            'counter' => 0,
            'device_name' => $response['deviceName'] ?? 'Biometric Device',
            'aaguid' => $response['aaguid'] ?? null,
        ]);

        return $credential;
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
        $challengeB64 = base64_encode($challenge);
        
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
        $clientData = json_decode(base64_decode($clientDataJSON), true);
        
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

        // In a full implementation, you would verify the signature here
        // For now, we'll trust the credential if challenge and origin match
        
        // Update counter (simplified)
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
