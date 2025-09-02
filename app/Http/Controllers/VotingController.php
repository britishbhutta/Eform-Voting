<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Tariff;
use App\Models\Booking;
use App\Models\Reward;
use App\Models\VotingEvent;
use App\Models\VotingEventOption;
use App\Models\VotingEventVote;
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
   
    private function getVotingEventTimezone($votingEvent)
    {
        if (!$votingEvent || !$votingEvent->booking_id) {
            return config('app.timezone', 'UTC');
        }

        $booking = Booking::find($votingEvent->booking_id);
        if (!$booking || !$booking->country) {
            return config('app.timezone', 'UTC');
        }

        $country = Country::find($booking->country);
        if (!$country || !$country->code) {
            return config('app.timezone', 'UTC');
        }

        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
        return $timezones[0] ?? config('app.timezone', 'UTC');
    }
    public function realized(Request $request)
    {
        if(auth()->check()){
            $bookings = Booking::where('user_id', auth()->id())->get();
            return view('voting.realized', compact('bookings'));
        }
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

        if($step === 3){
            if(isset($_GET['booking_id'])){
                $bookingId = $_GET['booking_id'];
                session(['booking_id' => $_GET['booking_id']]);
           }
        }
 
       
        $selectedId = session('voting.selected_tariff', null);
        if (session()->has('booking_id')) { 
            $bookingId = session('booking_id');
            $selectedId = Booking::where('id', $bookingId)->value('tariff_id');
          
        }
  
            // get booking id
        $bookingId = Booking::where('user_id', auth()->id())
            ->where('tariff_id', $selectedId)
            ->where('is_completed', '0')->value('id');
        
        $selectedTariff = $selectedId ? Tariff::find($selectedId) : null;
       
        
       
        if ($selectedId && ! $selectedTariff) {
            session()->forget('voting.selected_tariff');
            $selectedId = null;
            $selectedTariff = null;
        }
        
        
        $tariffs = null;
        if ($step === 1) {
            $tariffs = Tariff::orderBy('price_cents', 'asc')->get();
            session()->forget('booking_id');
            session()->forget('voting.voting_event_id');

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

                if (! $bookingId && Auth::check()) {
                    $latestBooking = Booking::where('user_id', Auth::id())
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestBooking) {
                        $bookingId = $latestBooking->id;
                    }
                }
                if (session()->has('booking_id')) {
                    $bookingId = session('booking_id');
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

                    $purchasedTariffId = session('voting.purchased_tariff_id');
                    if (! $purchasedTariffId && $bookingId) {
                        $purchasedTariffId = PurchasedTariff::where('booking_id', $bookingId)->value('id');
                    }

                    if ($votingEvent) {
                        $belongsToDifferentBooking = $bookingId && $votingEvent->booking_id !== $bookingId;
                        if ($belongsToDifferentBooking) {
                            $votingEvent = null;
                            session()->forget('voting.voting_event_id');
                        }
                    }

                 
                    $countryTimezone = config('app.timezone', 'UTC');
                    if ($bookingId) {
                        $booking = Booking::find($bookingId);
                        if ($booking && $booking->country) {
                            $country = Country::find($booking->country);
                            if ($country && $country->code) {
                                $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
                                $countryTimezone = $timezones[0] ?? config('app.timezone', 'UTC');
                            }
                        }
                    }

                    $votingPayload = [
                        'title'      => $data['form_name'],
                        'question'   => $data['question'],
                        'start_at'   => Carbon::createFromFormat('Y-m-d\\TH:i', $data['start_at'], $countryTimezone)->utc(),
                        'end_at'     => Carbon::createFromFormat('Y-m-d\\TH:i', $data['end_at'], $countryTimezone)->utc(),
                        'purchased_tariff_id' => $purchasedTariffId ?? null,
                        'booking_id' => $bookingId ?: null,
                        'status'     => 1,
                        'token'      => Str::uuid()->toString(),
                    ];

                    

                    if ($votingEvent) {
                        $votingEvent->update($votingPayload);
                    } else {
                     
                        if ($bookingId) {
                            $votingEvent = VotingEvent::updateOrCreate(
                                ['booking_id' => $bookingId],
                                $votingPayload
                            );
                        } else {
                            $votingEvent = VotingEvent::create($votingPayload);
                        }
                    }


                    $existingOptions = $votingEvent->options()->orderBy('id')->get();

                    $existingCount = $existingOptions->count();
                    $newCount = count($options);
                    $max = max($existingCount, $newCount);

                    for ($i = 0; $i < $max; $i++) {
                        $existing = $existingOptions[$i] ?? null;
                        $newText = $options[$i] ?? null;

                        if ($existing && $newText !== null) {
                            // Update text only; keep votes_count and status as-is
                            $existing->option_text = $newText;
                            $existing->save();
                        } elseif ($existing && $newText === null) {
                            // Extra old option -> delete it
                            $existing->delete();
                        } elseif (! $existing && $newText !== null) {
                            // New option beyond existing -> create it
                            $votingEvent->options()->create([
                                'option_text' => $newText,
                                'votes_count' => 0,
                                'status'      => 1,
                            ]);
                        }
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
        $localTime =  null;
        $timezone = null;
        

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

      
        if (($step === 4 || $step === 5) && $booking && $booking->country) {
            $country = Country::find($booking->country);
            if ($country && $country->code) {
                $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
                $timezone = $timezones[0] ?? 'UTC'; 
                $dt = new \DateTime('now', new \DateTimeZone($timezone));
                $gmtOffset = $dt->format('P'); 
                $localTime = ' — '. $country->name . ' ( ' . $timezone . ' , GMT ' . $gmtOffset . ' )';
            }
        }

        $votingData = [];
        if ($step === 4) {
            $sessionVoting = session('voting.voting', []);
           
            $dbVoting = null;

            if (session()->has('booking_id')) {
                $bookingId = session('booking_id');
            }

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
                    'start_at' => $dbVoting->start_at ? Carbon::parse($dbVoting->start_at)->setTimezone($timezone)->format('Y-m-d\\TH:i') : '',
                    'end_at'   => $dbVoting->end_at ? Carbon::parse($dbVoting->end_at)->setTimezone($timezone)->format('Y-m-d\\TH:i') : '',
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
            $country = Country::find( $booking->country);
            
            $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
            $timezone = $timezones[0] ?? 'UTC'; 
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            $gmtOffset = $dt->format('P'); 

            $localTime = ' — '. $country->name . ' ( ' . $timezone . ' , GMT ' . $gmtOffset . ' )';
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
            'localTime'     => $localTime,
            'timezone'      => $timezone,
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

     
        $timezone = $this->getVotingEventTimezone($votingEvent);
        $now = Carbon::now($timezone);
        if ($votingEvent->start_at && $now->lt($votingEvent->start_at)) {
            return view('voting.public.not-started', compact('votingEvent', 'timezone'));
        }

        if ($votingEvent->end_at && $now->gt($votingEvent->end_at)) {
            return view('voting.public.ended', compact('votingEvent', 'timezone'));
        }

        if (!Auth::check() || !Auth::user()->isVoter()) {
            session(['url.intended' => route('voting.public', ['token' => $token])]);
            return redirect()->route('login')->with('error', 'Please login as a voter to participate.');
        }

    
        $timezone = $this->getVotingEventTimezone($votingEvent);
        
        return view('voting.public.vote', compact('votingEvent', 'timezone'));
    }

    public function submitVote(Request $request, $token)
    {
        $votingEvent = VotingEvent::with('options')
            ->where('token', $token)
            ->where('status', 1)
            ->first();

        if (!$votingEvent) {
            abort(404, 'Voting event not found or inactive');
        }

        $timezone = $this->getVotingEventTimezone($votingEvent);
        $now = Carbon::now($timezone);
        if ($votingEvent->start_at && $now->lt($votingEvent->start_at)) {
            return back()->with('error', 'Voting has not started yet.');
        }

        if ($votingEvent->end_at && $now->gt($votingEvent->end_at)) {
            return back()->with('error', 'Voting has ended.');
        }

        
        if (!Auth::check() || !Auth::user()->isVoter()) {
            session(['url.intended' => route('voting.public', ['token' => $token])]);
            return redirect()->route('login')->with('error', 'Please login as a voter to participate.');
        }

        $request->validate([
            'selected_option' => 'required|exists:voting_event_options,id',
        ]);

        $email = strtolower(trim(Auth::user()->email));

        $alreadyVoted = VotingEventVote::where('voting_event_id', $votingEvent->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();
        if ($alreadyVoted) {
            return back()->with('error', 'You have already voted for this event.');
        }

        
        $purchasedTariff = $votingEvent->purchasedTariff;
        if (! $purchasedTariff && $votingEvent->booking_id) {
            $purchasedTariff = PurchasedTariff::where('booking_id', $votingEvent->booking_id)->first();
        }

        if (! $purchasedTariff || $purchasedTariff->remaining_votes <= 0) {
            return back()->with('error', 'No more votes available for this event.');
        }

        DB::beginTransaction();
        try {
          
            $selectedOption = VotingEventOption::find($request->selected_option);
            $selectedOption->increment('votes_count');
            $purchasedTariff->decrement('remaining_votes');
            $purchasedTariff->increment('votes_count');

            VotingEventVote::create([
                'voting_event_id' => $votingEvent->id,
                'voting_event_option_id' => $selectedOption->id,
                'email' => $email,
            ]);

            DB::commit();

            return redirect()->route('voting.success', [
                'token' => $token,
                'option' => $selectedOption->id,
            ])->with([
                'selected_option_id' => $selectedOption->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vote submission failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit vote. Please try again.');
        }
    }

    
    public function checkVotingStatus($token)
    {
        $votingEvent = VotingEvent::where('token', $token)
            ->where('status', 1)
            ->first(['start_at', 'end_at', 'status', 'booking_id']);

        if (!$votingEvent) {
            return response()->json(['status' => 'not_found'], 404);
        }

      
        $timezone = $this->getVotingEventTimezone($votingEvent);
        $now = Carbon::now($timezone);
        
        if ($votingEvent->start_at && $now->lt($votingEvent->start_at)) {
            return response()->json([
                'status' => 'not_started',
                'start_time' => $votingEvent->start_at->toISOString(),
                'current_time' => $now->toISOString(),
                'timezone' => $timezone
            ]);
        }

        if ($votingEvent->end_at && $now->gt($votingEvent->end_at)) {
            return response()->json([
                'status' => 'ended',
                'end_time' => $votingEvent->end_at->toISOString(),
                'current_time' => $now->toISOString(),
                'timezone' => $timezone
            ]);
        }

        return response()->json([
            'status' => 'active',
            'current_time' => $now->toISOString(),
            'timezone' => $timezone
        ]);
    }

  
    public function voteSuccess(Request $request, $token)
    {
        $votingEvent = VotingEvent::with('options')
            ->where('token', $token)
            ->where('status', 1)
            ->first();

        if (! $votingEvent) {
            abort(404, 'Voting event not found or inactive');
        }

        $selectedOption = null;
        $selectedOptionId = session('selected_option_id');
        if ($selectedOptionId) {
            $selectedOption = VotingEventOption::where('id', $selectedOptionId)
                ->where('voting_event_id', $votingEvent->id)
                ->first();
        }

       
        if (! $selectedOption) {
            $qpId = $request->query('option');
            if ($qpId) {
                $selectedOption = VotingEventOption::where('id', $qpId)
                    ->where('voting_event_id', $votingEvent->id)
                    ->first();
            }
        }


        if (! $selectedOption) {
            return redirect()->route('voting.public', ['token' => $token])
                ->with('status', 'Thank you for voting.');
        }

        return view('voting.public.success', compact('votingEvent', 'selectedOption'));
    }

  
    public function complete(Request $request)
    {
        $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();


        $errors = [];


        if (empty($booking->tariff_id)) {
            $errors[] = 'Tariff is not selected.';
        }


        if (empty($booking->payment_status) || strtolower($booking->payment_status) !== 'succeeded') {
            $errors[] = 'Payment has not been completed.';
        }


        $reward = $booking->reward;
        if (! $reward || empty(trim((string) $reward->name))) {
            $errors[] = 'Reward information is incomplete.';
        }

        
        $event = VotingEvent::with('options')->where('booking_id', $booking->id)->first();
        if (! $event) {
            $errors[] = 'Voting event is not created.';
        } else {
            if (empty(trim((string) $event->title)) || empty(trim((string) $event->question))) {
                $errors[] = 'Voting event title or question is missing.';
            }
            if (! $event->start_at || ! $event->end_at || $event->end_at->lte($event->start_at)) {
                $errors[] = 'Voting event start/end time is invalid.';
            }
            $optionCount = $event->options ? $event->options->filter(function ($o) { return !empty(trim((string)$o->option_text)); })->count() : 0;
            if ($optionCount < 2) {
                $errors[] = 'Please provide at least two voting options.';
            }
        }

        if (!empty($errors)) {
            return redirect()->route('voting.create.step', ['step' => 5])
                ->with('complete_errors', $errors)
                ->with('error', 'Please resolve the issues before finishing.');
        }

        $booking->is_completed = '1';
        $booking->booking_status = 'Completed';
        $booking->save();

        session()->forget('booking_id');
        session()->forget('voting.voting_event_id');
        session()->forget('voting.selected_tariff');

        return redirect()->route('voting.realized')->with('status', 'Voting form marked as completed.');
    }
}
