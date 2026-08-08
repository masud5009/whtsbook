<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class RoomNumberContent extends Model
{
    protected  $table = 'user_room_contents';
    protected $guarded = [];

    public function room()
    {
        return $this->belongsTo(RoomNumber::class, 'room_id', 'id');
    }
}
