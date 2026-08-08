<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class BasicExtended extends Model
{
    protected $table = 'user_basic_extendeds';
    protected $fillable = [
        'language_id',
        'user_id',
        'cookie_alert_status',
        'cookie_alert_btn_text',
        'cookie_alert_text'
    ];

    public function cookieAlertLang()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
