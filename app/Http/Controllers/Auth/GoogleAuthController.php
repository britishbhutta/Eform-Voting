<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google for authentication.
     * Store the chosen role (voter/creator) in session as integer so the callback can use it.
     */
    public function redirectToGoogle(Request $request)
    {
        $roleParam = $request->query('role', null);
        if ($roleParam === 'creator') {
            session(['signup_role' => User::ROLE_CREATOR]);
        } else {
            session(['signup_role' => User::ROLE_VOTER]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * We trust Google's email verification and therefore mark the user as active & verified.
     */
    public function handleGoogleCallback()
    {
        try {
            // If you run into session/state errors in some hosts, use stateless()
            $googleUser = Socialite::driver('google')->user();

            if (! $googleUser || ! $googleUser->getEmail()) {
                return redirect()->route('login')->with('error', 'No email returned from Google.');
            }

            $email = strtolower($googleUser->getEmail());
            $roleInt = session('signup_role', User::ROLE_VOTER);

            $data = [
                'first_name'        => $googleUser->user['given_name'] ?? '',
                'last_name'         => $googleUser->user['family_name'] ?? '',
                'google_id'         => $googleUser->getId(),
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => Carbon::now(),
                'is_active'         => true,
                'role'              => (int) $roleInt,
            ];

            // If user exists: update; otherwise create new user (email set)
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update($data);
            } else {
                $user = User::create(array_merge(['email' => $email], $data));
            }

            // Clear role from session
            session()->forget('signup_role');

            // Log user in
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $e) {
            Log::error('Google login error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('login')
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
