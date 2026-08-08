<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomReview extends Model
{
  use HasFactory;
  protected $table = 'user_room_reviews';

  protected $fillable = ['user_id', 'room_id', 'rating', 'comment', 'customer_id'];

  public function user()
  {
    return $this->belongsTo('App\Models\User', 'user_id', 'id');
  }

  public function reviewOfRoom()
  {
    return $this->belongsTo('App\Models\User\Room','room_id');
  }
}
