<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutGalleryImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'language_id',
        'image',
        'serial_number',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}

