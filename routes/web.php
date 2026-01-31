<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\BiometricAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'index']);

// Diagnostic route
Route::get('/diagnostic', function () {
    return view('diagnostic');
})->middleware('auth')->name('diagnostic');

// Biometric Authentication API Routes (using web middleware for session support)
Route::prefix('api/biometric')->name('api.biometric.')->group(function () {
    
    // Registration endpoints - require authenticated user
    Route::middleware(['auth'])->group(function () {
        Route::post('/register-options', [BiometricAuthController::class, 'registerOptions'])
            ->name('register.options');
        Route::post('/register-verify', [BiometricAuthController::class, 'registerVerify'])
            ->name('register.verify');
        Route::get('/credentials', [BiometricAuthController::class, 'getCredentials'])
            ->name('credentials.list');
        Route::delete('/credentials/{id}', [BiometricAuthController::class, 'deleteCredential'])
            ->name('credentials.delete');
    });
    
    // Login endpoints - public but rate limited
    Route::post('/login-options', [BiometricAuthController::class, 'loginOptions'])
        ->name('login.options');
    Route::post('/login-verify', [BiometricAuthController::class, 'loginVerify'])
        ->name('login.verify');
    
    // Enrollment completion endpoint (clears session flag)
    Route::post('/enrollment-complete', function() {
        session()->forget('biometric_enrollment_pending');
        return response()->json(['success' => true]);
    })->middleware('auth')->name('enrollment.complete');
});

// Customer Authentication Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.submit');
    });

    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [CustomerAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});
