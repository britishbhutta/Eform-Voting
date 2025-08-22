<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class VotingEvent extends Model
{

    protected $fillable = [
        'booking_id',
        'tariff_id',
        'title',
        'question',
        'start_at',
        'end_at',
        'status',
    ];

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function options()
    {
        return $this->hasMany(VotingEventOption::class);
    }
}
