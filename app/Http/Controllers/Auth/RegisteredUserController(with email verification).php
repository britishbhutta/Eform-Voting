<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // Accept ?role=voter or ?role=creator (default to 'voter')
        $selectedRole = $request->query('role', session('signup_role', old('role', 'voter')));

        // Load countries (adjust if you use an active() scope)
        $countries = Country::orderBy('name')->get();

        return view('auth.register', compact('countries', 'selectedRole'));
    }

    /**
     * Handle an incoming registration request.
     *
     * If a user already exists but is inactive (not verified), update their record,
     * issue a fresh verification code and resend the email. If the user exists and
     * is active, return a validation error for email.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input (we don't use unique:users,email here because we handle inactive users)
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => ['required', 'exists:countries,id'],
            'role'       => ['required', 'in:voter,creator'],
        ]);

        $email = strtolower($request->email);

        // Map friendly string to integer for DB
        $roleMap = [
            'voter'   => User::ROLE_VOTER,
            'creator' => User::ROLE_CREATOR,
        ];
        $roleInt = $roleMap[$request->role] ?? User::ROLE_VOTER;

        // Check for existing user with this email
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->is_active) {
                // Already active: behave like unique rule
                return back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
            }

            // Existing but not active -> update and resend verification
            $existing->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'password'   => Hash::make($request->password),
                'country_id' => $request->country_id,
                'role'       => $roleInt,
                'is_active'  => false,
            ]);

            $user = $existing;
        } else {
            // New inactive user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $email,
                'password'   => Hash::make($request->password),
                'country_id' => $request->country_id,
                'is_active'  => false,
                'role'       => $roleInt,
            ]);
        }

        // Fire Registered event in case other listeners exist
        event(new Registered($user));

        // Generate fresh 6-digit verification code
        try {
            $code = (string) random_int(100000, 999999);
        } catch (\Throwable $e) {
            $code = (string) rand(100000, 999999);
        }

        // Store hashed code and timestamps (overwrite previous)
        $user->update([
            'email_verification_code'       => Hash::make($code),
            'email_verification_sent_at'    => now(),
            'email_verification_expires_at' => now()->addMinutes(5),
        ]);

        // Send verification email
        $warning = null;
        try {
            Mail::to($user->email)->send(new WelcomeMail($code, $user->first_name));
        } catch (\Throwable $e) {
            Log::error('WelcomeMail send failed for user id ' . $user->id . ': ' . $e->getMessage());
            $warning = 'Verification email could not be sent right now. You can request a code on the verification page.';
        }

        if(session()->has('eventToken')){
            $token = session('eventToken');
        }else{
            $token = null;
        }

        // Ensure user is not logged in until they verify
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if($token){
            session(['eventToken' => $token]);
        }

        // Redirect to verification page with helpful flashes
        $redirect = redirect()->route('verification.notice')
            ->with('email_for_verification', $user->email)
            ->with('status', 'A verification code has been sent to your email.');

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }
}
