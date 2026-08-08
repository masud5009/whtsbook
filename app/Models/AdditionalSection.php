<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalSection extends Model
{
    protected $fillable = [
        'serial_number',
        'page_type',
        'possition',
    ];
    public function page_content()
    {
        return $this->belongsTo(AdditionalSection::class, 'addition_section_id', 'id');
    }
}