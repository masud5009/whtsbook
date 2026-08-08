<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomContent extends Model
{
  use HasFactory;
  protected $table = 'user_room_category_contents';

  protected $fillable = [
    'language_id',
    'room_category_id',
    'room_id',
    'title',
    'slug',
    'summary',
    'description',
    'amenities',
    'meta_keywords',
    'meta_description',
    'user_id'
  ];


  public function room()
  {
    return $this->belongsTo(Room::class,'room_id','id');
  }

  public function roomContentLang()
  {
    return $this->belongsTo('App\Models\User\Language');
  }
}
