<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Tariff;
use App\Models\Reward;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class VotingController extends Controller
{
    /**
     * Show realized votings (list).
     */
    public function realized(Request $request)
    {
        $votings = []; // placeholder
        return view('voting.realized', compact('votings'));
    }

    /**
     * Generic step handler for the creation wizard (GET -> render, POST -> handle form).
     *
     * Route: GET|POST /voting/create/step/{step}
     */
    public function step(Request $request, $step)
    {
        $step = (int) $step;

        $stepNames = [
            1 => 'Choose Tariff',
            2 => 'Personal Info & Payments',
            3 => 'Insert Reward',
            4 => 'Detail Of Event',
            5 => 'Creation Of Form',
        ];

        if ($step < 1 || $step > count($stepNames)) {
            abort(404);
        }

        // Load selected tariff id from session (if any) and the tariff model
        $selectedId = session('voting.selected_tariff', null);
        $selectedTariff = $selectedId ? Tariff::find($selectedId) : null;

        if ($selectedId && !$selectedTariff) {
            session()->forget('voting.selected_tariff');
            $selectedId = null;
            $selectedTariff = null;
        }

        // Step 1 shows tariffs
        $tariffs = null;
        if ($step === 1) {
            $tariffs = Tariff::orderBy('price_cents', 'asc')->get();
        } else {
            if (! $selectedTariff) {
                return redirect()->route('voting.create.step', ['step' => 1])
                    ->with('error', 'Tariff not exist in database.');
            }
        }

        // POST handling
        if ($request->isMethod('post')) {
            // STEP 1 POST: select tariff
            if ($step === 1 && $request->filled('tariff')) {
                $validated = $request->validate([
                    'tariff' => 'required|exists:tariffs,id',
                ]);

                session(['voting.selected_tariff' => (int) $validated['tariff']]);

                return redirect()->route('voting.create.step', ['step' => 2]);
            }

            // STEP 3 POST: reward creation / update
            if ($step === 3) {
                $validator = Validator::make($request->all(), [
                    'reward_name'        => ['required', 'string', 'max:255'],
                    'reward_description' => ['nullable', 'string', 'max:2000'],
                    'reward_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg', 'max:4096'],
                    'booking_id'         => ['nullable', 'integer', 'exists:bookings,id'],
                ]);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                // Build payload for Reward model
                $payload = [
                    'name'        => $request->input('reward_name'),
                    'description' => $request->input('reward_description'),
                ];

                // Store file if provided
                if ($request->hasFile('reward_image')) {
                    $path = $request->file('reward_image')->store('rewards', 'public');
                    $payload['image'] = $path;
                }

                // Determine booking id (from request or session)
                $bookingId = $request->input('booking_id') ?: session('voting.booking_id');

                if ($bookingId) {
                    $booking = Booking::find($bookingId);

                    if (! $booking) {
                        return back()->with('error', 'Booking not found.')->withInput();
                    }

                    // Try to find an existing reward for this booking
                    $existingReward = $booking->reward; // uses hasOne -> returns Reward or null

                    if ($existingReward) {
                        $existingReward->fill($payload);
                        $existingReward->save();
                        $reward = $existingReward;
                    } else {
                        // Create new reward linked to booking via relationship (booking_id will be set)
                        $reward = $booking->reward()->create($payload);
                    }

                    // ensure session stores booking id for later steps
                    session(['voting.booking_id' => $booking->id]);

                    return redirect()->route('voting.create.step', ['step' => 4])
                        ->with('success', 'Reward saved successfully.');
                }

                // No bookingId: create a standalone reward (booking_id left null)
                $reward = Reward::create($payload);

                return redirect()->route('voting.create.step', ['step' => 4])
                    ->with('success', 'Reward saved successfully.');
            }

            // default POST redirect back to step
            return redirect()->route('voting.create.step', ['step' => $step]);
        }

        // GET: render
        $countries = ($step === 2) ? Country::active()->orderBy('name')->get() : null;

        return view('voting.step', [
            'currentStep'    => $step,
            'stepNames'      => $stepNames,
            'tariffs'        => $tariffs,
            'tariff'         => $selectedTariff ? $selectedTariff->id : $request->query('tariff', null),
            'selectedTariff' => $selectedTariff,
            'countries'      => $countries,
        ]);
    }

    /**
     * Standalone endpoint to select tariff (stores id in session then redirects to step 2).
     */
    public function selectTariff(Request $request)
    {
        $validated = $request->validate([
            'tariff' => 'required|exists:tariffs,id',
        ]);

        session(['voting.selected_tariff' => (int) $validated['tariff']]);

        return redirect()->route('voting.create.step', ['step' => 2]);
    }
}
