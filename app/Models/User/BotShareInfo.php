<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BotShareInfo extends Model
{
    protected $table = 'bot_share_infos';

    protected $fillable = [
        'user_id',
        'hotel_name',
        'email_address',
        'phone_numbers',
        'locations',
        'services',
        'wp_id',
    ];

    protected $casts = [
        'email_address' => 'array',
        'phone_numbers' => 'array',
        'locations'     => 'array',
        'services'     => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
