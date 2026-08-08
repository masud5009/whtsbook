<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounterInformation extends Model
{
    protected $fillable = [
        'language_id',
        'icon',
        'color',
        'amount',
        'title',
    ];
}