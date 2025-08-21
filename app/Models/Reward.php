<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'name',
        'description',
        'image',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // helper to get public url for stored image (optional)
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . ltrim($this->image, '/')) : null;
    }
}
