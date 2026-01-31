<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('customer.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->with('error', 'Invalid email or password')->withInput($request->only('email'));
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return view('customer.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Log the user in
        Auth::login($user);
        
        // Regenerate session BEFORE firing event
        $request->session()->regenerate();

        // Fire the Registered event to trigger biometric enrollment
        event(new \Illuminate\Auth\Events\Registered($user));
        
        // Force save session to ensure flag persists
        $request->session()->save();

        return redirect()->route('customer.dashboard');
    }

    /**
     * Show customer dashboard
     */
    public function dashboard()
    {
        return view('customer.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')->with('success', 'You have been logged out successfully');
    }
}
