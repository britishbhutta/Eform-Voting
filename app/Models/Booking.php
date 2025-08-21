<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'company',
        'company_id',
        'tax_vat_no',
        'name',
        'address',
        'city',
        'zip',
        'country',
        'booking_reference',
        'price',
        'currency',
        'transaction_id',
        'payment_status',
        'payment_method',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * One-to-one relationship: a booking has one reward.
     */
    public function reward()
    {
        return $this->hasOne(Reward::class, 'booking_id', 'id');
    }
}
