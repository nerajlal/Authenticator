<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the biometric_credentials table for storing WebAuthn credentials.
     * Each credential is linked to a user and contains the public key and metadata
     * needed for biometric authentication.
     */
    public function up(): void
    {
        Schema::create('biometric_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('credential_id', 255)->unique()->comment('WebAuthn credential identifier');
            $table->text('public_key')->comment('Public key for signature verification');
            $table->unsignedBigInteger('counter')->default(0)->comment('Sign counter for replay attack prevention');
            $table->string('device_name', 100)->nullable()->comment('Optional user-friendly device name');
            $table->string('aaguid', 36)->nullable()->comment('Authenticator AAGUID');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('credential_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_credentials');
    }
};
