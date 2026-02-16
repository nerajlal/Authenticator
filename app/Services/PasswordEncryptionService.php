<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class PasswordEncryptionService
{
    /**
     * Encrypt a password for storage
     */
    public function encrypt(string $password): string
    {
        return Crypt::encryptString($password);
    }

    /**
     * Decrypt a stored password
     */
    public function decrypt(string $encryptedPassword): string
    {
        return Crypt::decryptString($encryptedPassword);
    }
}
