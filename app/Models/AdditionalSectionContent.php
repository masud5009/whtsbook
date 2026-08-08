<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalSectionContent extends Model
{
      protected $fillable = [
        'language_id',
        'addition_section_id',
        'section_name',
        'content',
    ];
}
