<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'language_id',
        'title',
        'subtitle',
        'description',
        'btn_name',
        'btn_url',
        'img',
        'serial_number',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
