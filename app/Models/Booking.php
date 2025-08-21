<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'tariff_id',
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
        'is_completed',
        'booking_status',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
