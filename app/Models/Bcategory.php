<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Bcategory extends Model
{
  public $timestamps = false;

  protected $fillable = [
    "language_id",
    "name",
    "slug",
    "status",
    "serial_number",
    'indx'
  ];

  public function blogs()
  {
    return $this->hasMany('App\Models\Blog', 'category_index', 'indx');
  }

  public function categoryContentList()
  {
    return $this->hasMany('App\Models\Bcategory', 'indx', 'indx');
  }

  public function scopeActive(Builder $query)
  {
    return $query->where('status', 1);
  }

  public function languagewiseCategories()
  {
    return $this->hasMany(Bcategory::class, 'indx', 'indx');
  }

  protected static function boot()
  {
    parent::boot();
    static::deleting(function ($category) {
      $category->languagewiseCategories()->delete();
    });
  }
}
