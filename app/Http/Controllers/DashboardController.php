<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect(){
        
        if (Booking::where('user_id', auth()->id())->exists()) {
            return redirect()->route('voting.realized');
        } else {
            return redirect()->route('voting.create.step', 1);
        }
    }
}
