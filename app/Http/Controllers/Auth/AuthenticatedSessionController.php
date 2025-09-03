<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validate hCaptcha response exists
        // $request->validate([
        //     'h-captcha-response' => 'required',
        // ]);

        // Verify hCaptcha with API
        $hcaptchaResponse = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret'   => env('HCAPTCHA_SECRET'),
            'response' => $request->input('h-captcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($hcaptchaResponse->json('success') ?? false)) {
            return back()
                ->withErrors(['h-captcha-response' => 'hCaptcha verification failed. Please try again.'])
                ->withInput($request->except('password'));
        }

        // Proceed with Laravel default login
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        
        // Check if user's email is verified before allowing login
        if (!$user->hasVerifiedEmail()) {
            // Log the user out immediately
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Redirect to verification page with error message
            return redirect()->route('verification.notice')
                ->with('email_for_verification', $user->email)
                ->with('error', 'Please verify your email address before logging in. A verification code has been sent to your email.');
        }
        
        // 🔑 Redirect by role
        if ($user->role == '2') {
            return redirect()->intended('/realized');
        }

        if ($user->role == '1') {
            return redirect()->intended('/voter');
        }

        // fallback (e.g. admin or others)
        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(\Illuminate\Http\Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
