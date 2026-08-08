<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;
    protected $table = 'user_room_categories';

    protected $fillable = [
        'slider_imgs',
        'featured_img',
        'status',
        'bed',
        'bath',
        'rent',
        'avg_rating',
        'user_id',
        'amenities_index',
        'category_index',
        'adult',
        'child',
        'payment_system',
        'advance_amount',
        'weekend_price',
        'regular_price',
        'seasonal_price',
        'details_link',
        'weekend',
        'seasonal_dates',
        'seasonal_weekend_price',
        'seasonal_weekend',
        'room_details_page',
    ];

    public function roomContent()
    {
        return $this->hasMany('App\Models\User\RoomContent');
    }

    public function roomBooking()
    {
        return $this->hasMany('App\Models\User\RoomBooking');
    }

    public function roomReview()
    {
        return $this->hasMany('App\Models\User\RoomReview');
    }

    public function numbers()
    {
        return $this->hasMany(RoomNumber::class, 'room_category_id', 'id');
    }

    /**
     * scope a query to only those rooms whose status is show.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query)
    {
        return $query->where('status', 1);
    }
}
