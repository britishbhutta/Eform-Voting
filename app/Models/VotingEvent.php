<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'purchased_tariff_id',
        'title',
        'question',
        'start_at',
        'end_at',
        'status',
        'token',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function purchasedTariff()
    {
        return $this->belongsTo(PurchasedTariff::class, 'purchased_tariff_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function options()
    {
        return $this->hasMany(VotingEventOption::class);
    }

    public function votes()
    {
        return $this->hasMany(VotingEventVote::class);
    }
}
