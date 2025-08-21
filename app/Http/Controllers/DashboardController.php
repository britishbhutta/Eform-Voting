<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect(){
        $bookings = Booking::where('user_id', auth()->id())->get();


        if ($bookings) {
            return view('voting.realized', compact('bookings'));
        }else{
            return redirect()->route('voting.create');
        }
        
    }
}
