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

// Biometric Enrollment & Auth Pages (redirect-based flow)
Route::prefix('biometric')->name('biometric.')->group(function () {
    Route::get('/enroll', [App\Http\Controllers\BiometricEnrollmentController::class, 'showEnrollment'])
        ->name('enroll');
    Route::get('/auth', [App\Http\Controllers\BiometricEnrollmentController::class, 'showAuth'])
        ->name('auth');
    Route::get('/login-bridge', [App\Http\Controllers\BiometricEnrollmentController::class, 'showLoginBridge'])
        ->name('login.bridge');
    Route::get('/shopify-login', [App\Http\Controllers\BiometricEnrollmentController::class, 'showShopifyLogin'])
        ->name('shopify.login');
    Route::post('/auth-success', [App\Http\Controllers\BiometricEnrollmentController::class, 'handleAuthSuccess'])
        ->name('auth.success');
});

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

// Shopify Integration API Routes
Route::prefix('api/shopify')->name('api.shopify.')->group(function () {
    // Customer sync endpoint - creates/updates user from Shopify customer data
    Route::post('/sync-customer', [App\Http\Controllers\ShopifyController::class, 'syncCustomer'])
        ->name('sync.customer');
    
    // Check if customer has biometric enrolled
    Route::post('/check-enrollment', [App\Http\Controllers\ShopifyController::class, 'checkEnrollment'])
        ->name('check.enrollment');
    
    // Get authenticated session for Shopify customer
    Route::post('/get-session', [App\Http\Controllers\ShopifyController::class, 'getSession'])
        ->name('get.session');
    
    // Shopify authentication endpoint
    Route::post('/authenticate', [App\Http\Controllers\ShopifyLoginController::class, 'authenticate'])
        ->name('authenticate');
});

// Custom Shopify Login Page
Route::get('/shopify/login', [App\Http\Controllers\ShopifyLoginController::class, 'showCustomLogin'])
    ->name('shopify.custom.login');

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
