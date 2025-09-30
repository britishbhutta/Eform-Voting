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

// Public API route to check voting status (for auto-refresh functionality)
Route::get('/api/voting/{token}/status', [VotingController::class, 'checkVotingStatus'])
    ->name('voting.status');



// Authenticated user routes (dashboard, profile) protected by auth + verified
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');
    // Dashboard: canonical dashboard route (redirect to the wizard start)
    // Route::get('/dashboard', function () {
    //     return redirect()->route('voting.create');
    // })->name('dashboard');

    
});
//Route::get('/dashboard',[DashboardController::class, 'redirect'])->name('dashboard');
// Middleware role:2 = Creator, role:1 = Voter
Route::middleware(['auth', 'verified','role:2'])->group(function () {

    Route::get('/voting/create', function () {
        return redirect()->route('voting.create.step', ['step' => 1]);
    })->name('voting.create');
    
    Route::match(['get', 'post'], '/voting/create/step/{step}', [VotingController::class, 'step'])
        ->whereNumber('step')
        ->name('voting.create.step');

    // Realized votings list
    Route::get('/realized', [VotingController::class, 'realized'])->name('voting.realized');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Stripe
    Route::post('payment',[StripeController::class, 'store'])->name('stripe.payment');

    Route::post('/voting/select-tariff', [VotingController::class, 'selectTariff'])->name('voting.select_tariff');

    // Mark booking as completed
    Route::post('/voting/complete', [VotingController::class, 'complete'])
        ->name('voting.complete');

    Route::post('/voting/set/{id}', function ($id) {
    return redirect()->route('voting.create.step', [
        'step' => 3,
        'booking_id' => $id
            ]);
        })->name('voting.set');
        Route::get('/terms', function () {
            return view('term-condition.terms-for-tariff-selection'); // resources/views/terms.blade.php
        })->name('terms.show');
});

// Middleware role:2 = Creator, role:1 = Voter
Route::middleware(['auth', 'verified','role:1'])->group(function () {
    Route::get('/voter', function(){
        return view('voting.voter.index');
    })->name('voter');
    // Public voting route for voters
    

    Route::post('/voting/{token}/submit', [VotingController::class, 'submitVote'])
        ->name('voting.submit');

    // PRG success page after submitting a vote
    Route::get('/voting/{token}/success', [VotingController::class, 'voteSuccess'])
        ->name('voting.success');
});
Route::get('/voting/{token}', [VotingController::class, 'publicVoting'])
        ->name('voting.public');
//Route::get('/votingSignIn/{token}', [VotingController::class, 'votingSignIn'])->name('votingSignIn');
