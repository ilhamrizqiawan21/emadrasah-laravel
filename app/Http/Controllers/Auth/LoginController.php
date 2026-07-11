<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Rate limiting - max 5 attempts per minute per IP
        $maxAttempts = 5;
        $decayMinutes = 1;
        $throttleKey = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withErrors([
                    'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
                ])
                ->withInput($request->only('email'));
        }
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);
            
            // Check if user is active
            if (!auth()->user()->is_active) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])
                    ->withInput($request->only('email'));
            }
            
            $request->session()->regenerate();
            
            // Log successful login (optional: add to activity log table)
            \Log::info('User login successful', [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return redirect()->intended('/dashboard');
        }
        
        // Increment failed attempts
        RateLimiter::hit($throttleKey, $decayMinutes * 60);

        // Log failed login attempt
        \Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}