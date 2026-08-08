<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AiTokenRecharge extends Model
{
    protected $table = 'ai_token_recharges';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
