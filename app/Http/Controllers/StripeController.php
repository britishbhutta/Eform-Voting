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



class StripeController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'stripeToken'   => 'required|string',
            // 'selectedTariffId' => 'required|exists:tariffs,id',
            // Billing info
            'cardholder_name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
       
        // Verify Cloudflare Turnstile
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($turnstileResponse->json('success') ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CAPTCHA verification failed. Please try again.'
            ], 422);
        }
        $validated = $validator->validated();
        // $country_id = (int) filter_var($validated['country'], FILTER_SANITIZE_NUMBER_INT);
        // $country = Country::find($country_id);
        
        if(session()->has('booking_id')){
            $booking = Booking::find(session('booking_id'));
        }
        $selectedTariff = Tariff::find($booking->tariff_id);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $charge = $stripe->charges->create([
            'amount' => $selectedTariff->price_cents,
            'currency' => $selectedTariff->currency,
            'source' => $request->stripeToken,
        ]);

        $validated = $validator->validated();
        // $booking = new Booking;
        // $booking->tariff_id        = $request->selectedTariffId;
        // $booking->user_id          = auth()->id();
        $booking->price            = $selectedTariff->price_cents / 100;
        $booking->currency         = $selectedTariff->currency;
        $booking->transaction_id   = $charge->id;
        $booking->payment_status   = $charge->status;
        $booking->payment_method   = 'stripe';
        $booking->save();

        try {
            $totalVotes = (int) ($selectedTariff->available_votes ?? 0);
            $purchased = PurchasedTariff::create([
                'booking_id'     => $booking->id,
                'tariff_id'      => $selectedTariff->id,
                'user_id'        => auth()->id() ?: null,
                'total_votes'    => $totalVotes,
                'remaining_votes' => $totalVotes,
                'token'          => (string) Str::uuid(),
                'is_active'      => true,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully but failed to create purchased tariff: ' . $e->getMessage(),
                'booking_id' => $booking->id,
            ]);
        }

        session([
            'voting.booking_id' => $booking->id,
            'voting.purchased_tariff_id' => $purchased->id,
            'voting.selected_tariff' => $selectedTariff->id,
        ]);

        $invoiceIssued = session('issued_invoice');
        if($invoiceIssued == 'true'){
            $user = Auth::user();
            Mail::to($booking->email)->send(new InvoiceIssueMail($user, $booking, $selectedTariff));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully!',
            'booking_id' => $booking->id,
            'purchased_tariff_id' => $purchased->id,
        ]);
    }
}
