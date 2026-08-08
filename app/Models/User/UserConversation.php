<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ticket_id',
        'reply',
        'file',
    ];

    // public function ticket()
    // {
    //     return $this->belongsTo('App\Models\User\UserTickect', 'ticket_id');
    // }
    // public function user()
    // {
    //     return $this->belongsTo('App\Models\User', 'user_id');
    // }
}
