<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

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
            return redirect()->route('voter');
        }
        
    }
}
