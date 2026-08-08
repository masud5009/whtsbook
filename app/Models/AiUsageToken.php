<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageToken extends Model
{
    protected $table = 'ai_useages';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
