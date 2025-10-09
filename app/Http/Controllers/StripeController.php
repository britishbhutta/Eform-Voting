<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceIssueMail;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Tariff;
use App\Models\PurchasedTariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\ApiErrorException;


class StripeController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'stripeToken'     => 'required|string',
            'cardholder_name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify Turnstile
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret_key'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($turnstileResponse->json('success') ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CAPTCHA verification failed. Please try again.'
            ], 422);
        }

        $booking = session()->has('booking_id') ? Booking::find(session('booking_id')) : null;
        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found in session'
            ], 404);
        }

        $selectedTariff = Tariff::find($booking->tariff_id);
        if (!$selectedTariff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tariff not found'
            ], 404);
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        try {
            // Try to charge
            $charge = $stripe->charges->create([
                'amount'   => (int) $selectedTariff->price_cents,
                'currency' => $selectedTariff->currency,
                'source'   => $request->stripeToken,
                'description' => 'Payment for booking #'.$booking->id,
            ]);
        } catch (ApiErrorException $e) {
            // Stripe rejected it before processing
            \Log::error('Stripe API error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed: '.$e->getMessage(),
            ], 402); // 402 Payment Required
        }

        // ✅ Only continue if payment succeeded
        if ($charge->status !== 'succeeded') {
            \Log::warning('Stripe charge not successful', ['charge' => $charge]);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed. Status: '.$charge->status,
            ], 402);
        }

        try {
            // Update booking after successful payment
            $booking->price          = $selectedTariff->price_cents / 100;
            $booking->currency       = $selectedTariff->currency;
            $booking->transaction_id = $charge->id;
            $booking->payment_status = $charge->status; // should be 'succeeded'
            $booking->payment_method = 'stripe';
            $booking->save();

            if ($booking->invoice_issue == 1) {
                $user = Auth::user();
                Mail::to($booking->email)->send(new InvoiceIssueMail($user, $booking, $selectedTariff));
            }

            $totalVotes = (int) ($selectedTariff->available_votes ?? 0);
            $purchased = PurchasedTariff::create([
                'booking_id'      => $booking->id,
                'tariff_id'       => $selectedTariff->id,
                'user_id'         => auth()->id() ?: null,
                'total_votes'     => $totalVotes,
                'remaining_votes' => $totalVotes,
                'token'           => (string) Str::uuid(),
                'is_active'       => true,
            ]);

        } catch (\Throwable $e) {
            \Log::error('Booking update error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment succeeded, but booking update failed: '.$e->getMessage(),
            ], 500);
        }

        session([
            'voting.booking_id'        => $booking->id,
            'voting.purchased_tariff_id' => $purchased->id,
            'voting.selected_tariff'   => $selectedTariff->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully!',
            'booking_id' => $booking->id,
            'purchased_tariff_id' => $purchased->id,
        ]);
    }


}
