<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class BookingAdjustment extends Model
{
    protected $table = 'booking_adjustments';

    protected $fillable = [
        'user_id',
        'booking_id',
        'grand_total',
        'amount',
        'type',
        'status'
    ];
}
