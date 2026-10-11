<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);


        $throttleKey = $this->throttleKey($request);

        # Allowed 5 attempts before the rate limiter prevents further attempts
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => "You have attempted to login unsuccessfully too many times. Please wait {$seconds} seconds to try again.",
            ]);
        }

        if(!Auth::guard('web')->attempt($credentials, false)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'username' => 'Incorrect username or password.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        
        # Return to the HomePage or page originally requested
        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        # Used to clear the session and regenerate the CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out.');
    }

    private function throttleKey(Request $request): string
    {
        $identity = Str::lower($request->input('username'))
            . '|'
            . $request->ip();
        return 'login' . hash('sha256', $identity);
    }

}