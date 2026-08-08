<?php

namespace App\Models\User;
use Illuminate\Database\Eloquent\Model;


class Language extends Model
{
    public $table = "user_languages";

    protected $fillable = [
        'id',
        'name',
        'is_default',
        'code',
        'rtl',
        'keywords',
        'user_id',
        'added_type',
        'dashboard_default'
    ];

    public function basic_extended()
    {
        return $this->hasOne('App\Models\BasicExtended', 'language_id');
    }

    public function packageDetails()
    {
        return $this->hasMany('App\Models\User\PackageContent');
    }
    public function roomDetails()
    {
        return $this->hasMany('App\Models\User\RoomContent');
    }
}
