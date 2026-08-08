<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoomAmenity extends Model
{
  use HasFactory;
  protected $table = 'user_room_amenities';

  protected $fillable = ['language_id', 'name', 'serial_number', 'user_id','indx','status'];

  public function amenityLang()
  {
    return $this->belongsTo('App\Models\Language');
  }

  public function user()
  {
    return $this->belongsTo('App\Models\User', 'user_id');
  }
  public function scopeActive(Builder $query)
  {
    return $query->where('status', 1);
  }
  
  public function scopeOwnedByUser(Builder $query)
  {
    return $query->where('user_id', Auth::guard('web')->user()->id);
  }
  
  public function languagewiseAmmenities(){
    return $this->hasMany(RoomAmenity::class ,'indx', 'indx');
  }
  
  protected static function boot()
  {
    parent::boot();
    static::deleting(function ($amenity) {
      $amenity->languagewiseAmmenities()->delete();
    });
  }
}
