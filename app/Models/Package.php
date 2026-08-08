<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public $table = "packages";

    protected $fillable = [
        'title',
        'slug',
        'price',
        'term',
        'featured',
        'is_trial',
        'recommended',
        'icon',
        'language_limit',
        'trial_days',
        'status',
        'room_categories_limit',
        'room_booking_limit',
        'room_limit',
        'features',
        'meta_keywords',
        'meta_description',
        'total_ai_token',
        'whatsapp_limit',
    ];
    public function memberships()
    {
        return $this->hasMany('App\Models\Membership');
    }
}
