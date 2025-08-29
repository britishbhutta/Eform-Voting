<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tariff;
use App\Models\PurchasedTariff;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;



class StripeController extends Controller
{
    public function store(Request $request)
    {

        $rules = [
            'stripeToken'   => 'required|string',
            'selectedTariffId' => 'required|exists:tariffs,id',


            // Billing info
            'email'         => 'required|email',
            'phone_number'  => 'nullable|string|max:20',
            'invoice_issued' => 'nullable|boolean',
            'company_name'  => 'required_if:invoice_issued,1|string|max:255',
            'company_id'    => 'required_if:invoice_issued,1|string|max:255',
            'tax_vat'       => 'nullable|string|max:50',
            'fname'         => 'required|string|max:100',
            'lname'         => 'required|string|max:100',
            'address'       => 'required|string|max:255',
            'city'          => 'required|string|max:100',
            'zip'           => 'required|string|max:20',
            'country'       => 'required|string',
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

        $selectedTariff = Tariff::find($request->selectedTariffId);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $charge = $stripe->charges->create([
            'amount' => $selectedTariff->price_cents,
            'currency' => $selectedTariff->currency,
            'source' => $request->stripeToken,
        ]);

        $validated = $validator->validated();
        $booking = new Booking;
        $booking->tariff_id        = $request->selectedTariffId;
        $booking->user_id          = auth()->id();

        $booking->email            = $validated['email'];
        $booking->phone            = $validated['phone_number'] ?? null;
        $booking->company          = $validated['company_name'] ?? null;
        $booking->company_id       = $validated['company_id'] ?? null;
        $booking->tax_vat_no       = $validated['tax_vat'] ?? null;
        $booking->name             = $validated['fname'] . ' ' . $validated['lname'];
        $booking->address          = $validated['address'];
        $booking->city             = $validated['city'];
        $booking->zip              = $validated['zip'];
        $booking->country          = $validated['country'];

        $booking->booking_reference = strtoupper(uniqid('BOOK-'));
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


        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully!',
            'booking_id' => $booking->id,
            'purchased_tariff_id' => $purchased->id,
        ]);
    }
}
