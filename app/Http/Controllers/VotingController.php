<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Tariff;
use App\Models\Reward;
use App\Models\VotingEvent;
use App\Models\VotingEventOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VotingController extends Controller
{

    public function realized(Request $request)
    {
        $bookings = Booking::where('user_id', auth()->id())->get();
        return view('voting.realized', compact('bookings'));
    }

    public function step($step, Request $request,)
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

        if (session('inCom_selected_tariff')) {
            echo 'here';
            $inComBooking = Booking::find(session('inCom_selected_tariff'));
            $selectedId = $inComBooking->tariff_id;
            session()->forget('inCom_selected_tariff');
        }else{
            echo 'else';
            $selectedId = session('voting.selected_tariff', null);
        }

        $selectedTariff = $selectedId ? Tariff::find($selectedId) : null;

        if ($selectedId && ! $selectedTariff) {
            session()->forget('voting.selected_tariff');
            $selectedId = null;
            $selectedTariff = null;
        }

        $tariffs = null;
        if ($step === 1) {
            $tariffs = Tariff::orderBy('price_cents', 'asc')->get();
        } else {
            if (! $selectedTariff) {
                return redirect()->route('voting.create.step', ['step' => 1])
                    ->with('error', 'Tariff not exist in database.');
            }
        }

        if ($request->isMethod('post')) {

            if ($step === 1 && $request->filled('tariff')) {
                $validated = $request->validate([
                    'tariff' => 'required|exists:tariffs,id',
                ]);

                session(['voting.selected_tariff' => (int) $validated['tariff']]);

                return redirect()->route('voting.create.step', ['step' => 2]);
            }

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

                $payload = [
                    'name'        => $request->input('reward_name'),
                    'description' => $request->input('reward_description'),
                ];

                if ($request->hasFile('reward_image')) {
                    $path = $request->file('reward_image')->store('rewards', 'public');
                    $payload['image'] = $path;
                }

                $bookingId = $request->input('booking_id') ?: session('voting.booking_id');


                if (! $bookingId && Auth::check()) {
                    $latestBooking = Booking::where('user_id', Auth::id())
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestBooking) {
                        $bookingId = $latestBooking->id;
                    }
                }


                if (! $bookingId) {
                    return back()->with('error', 'No booking found. Please complete payment or select a booking before saving a reward.')->withInput();
                }

                $booking = Booking::find($bookingId);
                if (! $booking) {
                    return back()->with('error', 'Booking not found.')->withInput();
                }


                try {
                    $reward = Reward::updateOrCreate(
                        ['booking_id' => $booking->id],
                        $payload
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to create/update reward: ' . $e->getMessage());
                    return back()->with('error', 'Unable to save reward. Please try again.')->withInput();
                }

                session(['voting.booking_id' => $booking->id]);

                return redirect()->route('voting.create.step', ['step' => 4])
                    ->with('success', 'Reward saved successfully.');
            }

            if ($step === 4) {
                $validator = Validator::make($request->all(), [
                    'form_name'  => ['required', 'string', 'max:255'],
                    'question'   => ['required', 'string', 'max:1000'],
                    'start_at'   => ['required', 'date'],
                    'end_at'     => ['required', 'date', 'after:start_at'],
                    'options'    => ['required', 'array', 'min:2'],
                    'options.*'  => ['nullable', 'string', 'max:500'],
                ]);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $data = $validator->validated();

                $options = array_values(array_filter(array_map('trim', $data['options']), function ($v) {
                    return $v !== '';
                }));

                if (count($options) < 2) {
                    return back()->withErrors(['options' => 'Please provide at least two voting options.'])->withInput();
                }

                DB::beginTransaction();
                try {
                    $votingPayload = [
                        'title'      => $data['form_name'],
                        'question'   => $data['question'],
                        'start_at'   => $data['start_at'],
                        'end_at'     => $data['end_at'],
                        'tariff_id'  => session('voting.selected_tariff') ?: null,
                        'booking_id' => session('voting.booking_id') ?: null,
                        'status'     => 1,
                    ];

                    $votingEvent = VotingEvent::create($votingPayload);

                    $optionRows = array_map(function ($opt) {
                        return [
                            'option_text' => $opt,
                            'votes_count' => 0,
                            'status'      => 1,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                    }, $options);

                    $votingEvent->options()->createMany($optionRows);

                    session(['voting.voting_event_id' => $votingEvent->id]);

                    DB::commit();

                    return redirect()->route('voting.create.step', ['step' => 5])
                        ->with('success', 'Voting event saved successfully.');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Failed to save voting event: ' . $e->getMessage());
                    return back()->with('error', 'Unable to save voting event. Please try again.')->withInput();
                }
            }

            return redirect()->route('voting.create.step', ['step' => $step]);
        }

  
        $countries = ($step === 2) ? Country::active()->orderBy('name')->get() : null;
        $booking = Booking::where('user_id',auth()->id())
        ->where('tariff_id',$selectedId)
        ->where('is_completed','0')
        ->orderBy('id','desc')->first();
        // Always pass both variables (tariffs may be null for steps > 1)
        return view('voting.step', [
            'currentStep' => $step,
            'stepNames' => $stepNames,
            'tariffs' => $tariffs,
            'booking' => $booking,

            'selectedTariff' => $selectedTariff,
            'countries'      => $countries,
        ]);
    }


    public function selectTariff(Request $request)
    {
        $validated = $request->validate([
            'tariff' => 'required|exists:tariffs,id',
        ]);

        session(['voting.selected_tariff' => (int) $validated['tariff']]);

        return redirect()->route('voting.create.step', ['step' => 2]);
    }
}
