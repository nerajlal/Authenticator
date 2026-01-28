<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

/**
 * Biometric Enrollment Listener
 * 
 * Listens to authentication events (Login and Registered) and sets a session flag
 * to trigger the biometric enrollment popup for users who don't have credentials yet.
 */
class SetBiometricEnrollmentFlag
{
    /**
     * Handle the Login event.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;

        // Check if user already has biometric credentials
        if ($user->biometricCredentials()->exists()) {
            return;
        }

        // Set session flag to show enrollment popup
        session(['biometric_enrollment_pending' => true]);

        Log::info('Biometric enrollment flag set for user after login', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Handle the Registered event.
     */
    public function handleRegistered(Registered $event): void
    {
        $user = $event->user;

        // Set session flag to show enrollment popup
        session(['biometric_enrollment_pending' => true]);

        Log::info('Biometric enrollment flag set for user after registration', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Registered::class => 'handleRegistered',
        ];
    }
}
