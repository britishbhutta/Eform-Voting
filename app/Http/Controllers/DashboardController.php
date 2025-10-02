<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\VotingEventVote;
use Illuminate\Http\Request;
use Auth;

class DashboardController extends Controller
{
    public function redirect(){
        if (auth()->user()->role == 2) {
            return Booking::where('user_id', auth()->id())->exists()
                ? redirect()->route('voting.realized')
                : redirect()->route('voting.create.step', 1);
        }
        $token = session('eventToken');
        if ($token) {
            session()->forget('eventToken');
            return redirect()->route('voting.public',[$token]);
        }else{
            return redirect()->route('voterHistory');
        }
        
    }

    public function voterHistory(){
        $votingEventVotes = VotingEventVote::where('email', Auth::user()->email)->get();
        return view('voting.voter.index', compact('votingEventVotes'));
    }
}
