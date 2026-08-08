<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomNumber extends Model
{
    use HasFactory;
    protected $table = 'user_rooms';

    protected $fillable = ['user_id', 'language_id', 'room_category_id', 'status'];

    public function categoryContents()
    {
        return $this->hasMany(RoomContent::class, 'room_id', 'room_category_id');
    }

    public function contents()
    {
        return $this->hasMany(RoomNumberContent::class, 'room_id', 'id');
    }
}
