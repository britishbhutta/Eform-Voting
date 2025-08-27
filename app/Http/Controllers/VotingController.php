<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Tariff;
use App\Models\Booking;
use App\Models\Reward;
use App\Models\VotingEvent;
use App\Models\VotingEventOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PurchasedTariff;

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

            $selectedId = session('voting.selected_tariff', null);
           
        $selectedTariff = $selectedId ? Tariff::find($selectedId) : null;

        if ($selectedId && ! $selectedTariff) {
            session()->forget('voting.selected_tariff');
            $selectedId = null;
            $selectedTariff = null;
        }
        // get bookings 
        $bookingId = Booking::where('user_id', auth()->id())
            ->where('tariff_id', $selectedId)
            ->where('is_completed', '0')->value('id');
        // print_r($bookingId);die;
        // 
        $tariffs = null;
        if ($step === 1) {
            $tariffs = Tariff::orderBy('price_cents', 'asc')->get();
            session()->forget('booking_id');

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
                    'existing_reward_image' => ['nullable', 'string'],
                    'booking_id'         => ['nullable', 'integer', 'exists:bookings,id'],
                ]);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $rewardDraft = [
                    'reward_name' => $request->input('reward_name'),
                    'reward_description' => $request->input('reward_description'),
                    'reward_image' => null,
                ];

                if ($request->hasFile('reward_image')) {
                    $path = $request->file('reward_image')->store('rewards', 'public');
                    $rewardDraft['reward_image'] = $path;
                } elseif ($request->filled('existing_reward_image')) {
                    $rewardDraft['reward_image'] = $request->input('existing_reward_image');
                }

                session(['voting.reward' => $rewardDraft]);

               
                if (! $bookingId) {
                    return back()->with('error', 'No booking found. Please complete payment or select a booking before saving a reward.')->withInput();
                }


                $payload = [
                    'name' => $rewardDraft['reward_name'],
                    'description' => $rewardDraft['reward_description'],
                ];
                if (!empty($rewardDraft['reward_image'])) {
                    $payload['image'] = $rewardDraft['reward_image'];
                }
                if (session()->has('booking_id')) {
                    $bookingId = session('booking_id');
                }
                try {
                    $reward = Reward::updateOrCreate(
                        ['booking_id' => $bookingId],
                        $payload
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to create/update reward: ' . $e->getMessage());
                    return back()->with('error', 'Unable to save reward. Please try again.')->withInput();
                }

                

                if (!empty($reward->image) && Storage::disk('public')->exists($reward->image)) {
                    $currentDraft = session('voting.reward', []);
                    $currentDraft['reward_image'] = $reward->image;
                    session(['voting.reward' => $currentDraft]);
                } else {
                    $currentDraft = session('voting.reward', []);
                    if (isset($currentDraft['reward_image'])) {
                        unset($currentDraft['reward_image']);
                        session(['voting.reward' => $currentDraft]);
                    }
                }

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
                    'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
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

                // $bookingId = $request->input('booking_id') ?: session('voting.booking_id');
                if (! $bookingId && Auth::check()) {
                    $latestBooking = Booking::where('user_id', Auth::id())
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestBooking) {
                        $bookingId = $latestBooking->id;
                    }
                }

                DB::beginTransaction();
                try {
                    $existingEventId = session('voting.voting_event_id', null);

                    $votingEvent = null;
                    if ($existingEventId) {
                        $votingEvent = VotingEvent::find($existingEventId);
                    }

                    if (! $votingEvent && $bookingId) {
                        $votingEvent = VotingEvent::where('booking_id', $bookingId)->first();
                    }

                    // Resolve purchased tariff id from session or via booking fallback
                    $purchasedTariffId = session('voting.purchased_tariff_id');
                    if (! $purchasedTariffId && $bookingId) {
                        $purchasedTariffId = PurchasedTariff::where('booking_id', $bookingId)->value('id');
                    }

                    $votingPayload = [
                        'title'      => $data['form_name'],
                        'question'   => $data['question'],
                        'start_at'   => Carbon::createFromFormat('Y-m-d\\TH:i', $data['start_at'], config('app.timezone')),
                        'end_at'     => Carbon::createFromFormat('Y-m-d\\TH:i', $data['end_at'], config('app.timezone')),
                        'purchased_tariff_id' => $purchasedTariffId ?? null,
                        'booking_id' => $bookingId ?: null,
                        'status'     => 1,
                        'token'      => Str::uuid()->toString(),
                    ];

                    // 

                    if ($votingEvent) {
                        $votingEvent->update($votingPayload);

                        $votingEvent->options()->delete();

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
                    } else {
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
                    }

                    session(['voting.voting_event_id' => $votingEvent->id]);
                    if ($bookingId) session(['voting.booking_id' => $bookingId]);

                    DB::commit();

                    return redirect()->route('voting.create.step', ['step' => 5])
                        ->with('success', 'Voting event saved successfully.');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Failed to save voting event: ' . $e->getMessage());
                    return back()->with('error', 'Unable to save voting event. Please try again.')->withInput();
                }
            }
            //
            return redirect()->route('voting.create.step', ['step' => $step]);
        }

        $countries = ($step === 2) ? Country::active()->orderBy('name')->get() : null;

        $booking = Booking::where('user_id',auth()->id())
        ->where('tariff_id',$selectedId)
        ->where('is_completed','0')
        ->orderBy('id','desc')->first();
        // Always pass both variables (tariffs may be null for steps > 1)

        $rewardData = [];
        if ($step === 3) {
            
           if(isset($_GET['booking_id'])){
                $bookingId = $_GET['booking_id'];
                session(['booking_id' => $_GET['booking_id']]);
           }
            
            $dbReward = Reward::where('booking_id', $bookingId)->first();
            
                $dbValues = [
                    'reward_name' => $dbReward->name?? null,
                    'reward_description' => $dbReward->description?? null,
                    'reward_image' => $dbReward->image ?? null,
                ];
            

            $rewardData = array_merge(
                [
                    'reward_name' => '',
                    'reward_description' => '',
                    'reward_image' => null,
                ],
                $dbValues,
              
                old()
            );
        }

        $votingData = [];
        if ($step === 4) {
            $sessionVoting = session('voting.voting', []);
           
            $dbVoting = null;

            if (session()->has('booking_id')) {
                $bookingId = session('booking_id');
            }
           

             print_r($bookingId);die;
            
            if ($bookingId) {
                $dbVoting = VotingEvent::with('options')->where('booking_id' ,$bookingId)->first();
            }

            $defaultVoting = [
                'form_name' => '',
                'question' => '',
                'start_at' => '',
                'end_at' => '',
                'options' => [],
            ];

            $dbValues = [];
            if ($dbVoting) {
                $dbValues = [
                    'form_name' => $dbVoting->title,
                    'question' => $dbVoting->question,
                    'start_at' => $dbVoting->start_at ? Carbon::parse($dbVoting->start_at)->format('Y-m-d\\TH:i') : '',
                    'end_at'   => $dbVoting->end_at ? Carbon::parse($dbVoting->end_at)->format('Y-m-d\\TH:i') : '',
                    'options'  => $dbVoting->options->pluck('option_text')->toArray(),
                ];
            }

            $votingData = array_merge($defaultVoting, $dbValues, $sessionVoting, old());
        }


        // Get voting event for step 5
        $votingEvent = null;
        if ($step === 5) {
            $existingEventId = session('voting.voting_event_id', null);
            if ($existingEventId) {
                $votingEvent = VotingEvent::with('options')->find($existingEventId);
            }
            
            if (!$votingEvent && $booking) {
                $votingEvent = VotingEvent::with('options')->where('booking_id', $booking->id)->first();
            }
        }


        return view('voting.step', [
            'currentStep' => $step,
            'stepNames' => $stepNames,
            'tariffs' => $tariffs,
            'booking' => $booking,
            'selectedTariff' => $selectedTariff,
            'countries'      => $countries,
            'rewardData'     => $rewardData,
            'votingData'     => $votingData,
            'votingEvent'    => $votingEvent,
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

    /**
     * Public voting access for voters using token
     */
    public function publicVoting($token)
    {
        $votingEvent = VotingEvent::with('options')
            ->where('token', $token)
            ->where('status', 1)
            ->first();

        if (!$votingEvent) {
            abort(404, 'Voting event not found or inactive');
        }

        // Check if voting is still active (within start/end dates)
        $now = Carbon::now();
        if ($votingEvent->start_at && $now->lt($votingEvent->start_at)) {
            return view('voting.public.not-started', compact('votingEvent'));
        }

        if ($votingEvent->end_at && $now->gt($votingEvent->end_at)) {
            return view('voting.public.ended', compact('votingEvent'));
        }

        return view('voting.public.vote', compact('votingEvent'));
    }

    /**
     * Submit a vote and decrease remaining votes count
     */
    public function submitVote(Request $request, $token)
    {
        $votingEvent = VotingEvent::with('options')
            ->where('token', $token)
            ->where('status', 1)
            ->first();

        if (!$votingEvent) {
            abort(404, 'Voting event not found or inactive');
        }

        // Check if voting is still active
        $now = Carbon::now();
        if ($votingEvent->start_at && $now->lt($votingEvent->start_at)) {
            return back()->with('error', 'Voting has not started yet.');
        }

        if ($votingEvent->end_at && $now->gt($votingEvent->end_at)) {
            return back()->with('error', 'Voting has ended.');
        }

        // Validate the request
        $request->validate([
            'selected_option' => 'required|exists:voting_event_options,id',
        ]);

        // Resolve purchased tariff (relation or fallback via booking)
        $purchasedTariff = $votingEvent->purchasedTariff;
        if (! $purchasedTariff && $votingEvent->booking_id) {
            $purchasedTariff = PurchasedTariff::where('booking_id', $votingEvent->booking_id)->first();
        }

        if (! $purchasedTariff || $purchasedTariff->remaining_votes <= 0) {
            return back()->with('error', 'No more votes available for this event.');
        }

        DB::beginTransaction();
        try {
            // Increment the vote count for the selected option
            $selectedOption = VotingEventOption::find($request->selected_option);
            $selectedOption->increment('votes_count');

            // Decrease the remaining votes count
            $purchasedTariff->decrement('remaining_votes');

            DB::commit();

            return view('voting.public.success', compact('votingEvent', 'selectedOption'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vote submission failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit vote. Please try again.');
        }
    }
}
