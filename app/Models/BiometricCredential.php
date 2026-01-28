<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BiometricCredential Model
 * 
 * Represents a WebAuthn credential registered for biometric authentication.
 * Each credential is linked to a user and stores the public key and metadata
 * needed to verify biometric authentication attempts.
 */
class BiometricCredential extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'biometric_credentials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'device_name',
        'aaguid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'counter' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the credential.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decoded public key.
     *
     * @return array|null
     */
    public function getDecodedPublicKey(): ?array
    {
        if (!$this->public_key) {
            return null;
        }
        
        return json_decode($this->public_key, true);
    }

    /**
     * Set the public key from an array.
     *
     * @param array $publicKey
     * @return void
     */
    public function setPublicKeyFromArray(array $publicKey): void
    {
        $this->public_key = json_encode($publicKey);
    }
}
