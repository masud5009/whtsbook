<?php

namespace App\Models;

use App\Models\User\Journal\BlogInformation;
use Illuminate\Database\Eloquent\Model;


class Blog extends Model
{
  public $timestamps = true;

  protected $fillable = [
    "language_id",
    "category_index",
    "title",
    "slug",
    "main_image",
    "content",
    "meta_keywords",
    "meta_description",
    "serial_number",
  ];

  public function bcategory()
  {
    return $this->belongsTo('App\Models\Bcategory', 'category_index', 'indx');
  }


  public function language()
  {
    return $this->belongsTo('App\Models\Language');
  }

  public function information()
  {
    return $this->hasMany(BlogInformation::class, 'blog_id');
  }
}
