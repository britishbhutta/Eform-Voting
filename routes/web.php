<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Public & auth routes
|--------------------------------------------------------------------------
*/

// Root: role chooser page (public)
Route::get('/', function () {
    return view('auth.choose-role');
})->name('home');

// include Breeze / Fortify auth routes (login/register/password/etc.)
require __DIR__ . '/auth.php';

// Public routes for Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])
    ->name('google.callback');

// Public Email verification routes (code-based verification)
Route::get('email/verify', [EmailVerificationController::class, 'show'])
    ->name('verification.notice');

Route::post('email/verify', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::post('email/resend', [EmailVerificationController::class, 'resend'])
    ->name('verification.resend');

// Public voting route for voters
Route::get('/voting/{token}', [VotingController::class, 'publicVoting'])
    ->name('voting.public');

Route::post('/voting/{token}/submit', [VotingController::class, 'submitVote'])
    ->name('voting.submit');

// PRG success page after submitting a vote
Route::get('/voting/{token}/success', [VotingController::class, 'voteSuccess'])
    ->name('voting.success');

// Authenticated user routes (dashboard, profile) protected by auth + verified
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard: canonical dashboard route (redirect to the wizard start)
    // Route::get('/dashboard', function () {
    //     return redirect()->route('voting.create');
    // })->name('dashboard');

    Route::get('/dashboard',[DashboardController::class, 'redirect'])->name('dashboard');

    // Canonical "create" entrypoint — redirects to step 1
    Route::get('/voting/create', function () {
        return redirect()->route('voting.create.step', ['step' => 1]);
    })->name('voting.create');

    // Wizard step route (1..5)
    // Route::get('/voting/create/step/{step}', [VotingController::class, 'step'])
    //     ->whereNumber('step')
    //     ->name('voting.create.step');

    Route::match(['get', 'post'], '/voting/create/step/{step}', [VotingController::class, 'step'])
        ->whereNumber('step')
        ->name('voting.create.step');

    // Realized votings list
    Route::get('/voting/realized', [VotingController::class, 'realized'])
        ->name('voting.realized');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Stripe
    Route::post('payment',[StripeController::class, 'store'])->name('stripe.payment');

    Route::post('/voting/select-tariff', [VotingController::class, 'selectTariff'])->name('voting.select_tariff');

    Route::post('/voting/set/{id}', function ($id) {
    return redirect()->route('voting.create.step', [
        'step' => 3,
        'booking_id' => $id
            ]);
        })->name('voting.set');
});
