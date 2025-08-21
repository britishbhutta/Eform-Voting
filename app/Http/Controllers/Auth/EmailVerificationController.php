<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function show(Request $request): View
    {
        $email = session('email_for_verification') ?? $request->input('email', '');
        return view('auth.verify-email', ['email' => $email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account found for this email.'])->withInput();
        }

        if ($user->email_verification_expires_at && now()->gt($user->email_verification_expires_at)) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new code.']);
        }

        if (! $user->email_verification_code || ! Hash::check($request->code, $user->email_verification_code)) {
            return back()->withErrors(['code' => 'Invalid verification code.'])->withInput();
        }

        // mark verified + activate
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->email_verification_code = null;
        $user->email_verification_sent_at = null;
        $user->email_verification_expires_at = null;
        $user->save();

        // Do NOT log the user in. Redirect to login with success.
        return redirect()->route('login')->with('success', 'Your email has been verified. Please login with your credentials.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account found for this email.']);
        }

        if ($user->email_verification_sent_at && now()->diffInSeconds($user->email_verification_sent_at) < 60) {
            return back()->withErrors(['email' => 'Please wait before requesting another code.']);
        }

        try {
            $code = (string) random_int(100000, 999999);
        } catch (\Throwable $e) {
            $code = (string) rand(100000, 999999);
        }

        $user->email_verification_code = Hash::make($code);
        $user->email_verification_sent_at = now();
        $user->email_verification_expires_at = now()->addMinutes(60);
        $user->save();

        try {
            Mail::to($user->email)->send(new WelcomeMail($code, $user->first_name));
        } catch (\Throwable $e) {
            Log::error('Failed resending verification code for user id ' . $user->id . ': ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send verification email. Please try again later.']);
        }

        return back()->with('status', 'A new verification code was sent to your email.');
    }
}
