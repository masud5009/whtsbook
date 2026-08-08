<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTicket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany('App\Models\User\UserConversation','ticket_id');
    }
   
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
