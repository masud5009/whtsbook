<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformModule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'language_id',
        'icon',
        'title',
        'subtitle',
        'image',
        'serial_number',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
