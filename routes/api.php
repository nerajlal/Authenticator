<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BiometricAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Biometric Authentication Routes
Route::prefix('biometric')->name('biometric.')->group(function () {
    
    // Registration endpoints - require authenticated user
    Route::middleware(['auth'])->group(function () {
        Route::post('/register-options', [BiometricAuthController::class, 'registerOptions'])
            ->name('register.options');
        Route::post('/register-verify', [BiometricAuthController::class, 'registerVerify'])
            ->name('register.verify');
    });
    
    // Login endpoints - public but rate limited
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::post('/login-options', [BiometricAuthController::class, 'loginOptions'])
            ->name('login.options');
        Route::post('/login-verify', [BiometricAuthController::class, 'loginVerify'])
            ->name('login.verify');
    });
    
    // Get user's registered credentials - requires auth
    Route::middleware(['auth'])->group(function () {
        Route::get('/credentials', [BiometricAuthController::class, 'getCredentials'])
            ->name('credentials.list');
        Route::delete('/credentials/{id}', [BiometricAuthController::class, 'deleteCredential'])
            ->name('credentials.delete');
    });
    
    // Enrollment completion endpoint (clears session flag)
    Route::post('/enrollment-complete', function() {
        session()->forget('biometric_enrollment_pending');
        return response()->json(['success' => true]);
    })->name('enrollment.complete');
});
