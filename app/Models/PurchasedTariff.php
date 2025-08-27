<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchasedTariff extends Model
{
    use HasFactory;

    protected $table = 'purchased_tariffs';

    protected $fillable = [
        'booking_id',
        'tariff_id',
        'user_id',
        'total_votes',
        'remaining_votes',
        'token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_votes' => 'integer',
        'remaining_votes' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votingEvent()
    {
        return $this->hasOne(VotingEvent::class, 'purchased_tariff_id');
    }
}
