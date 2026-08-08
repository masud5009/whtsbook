<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class BasicSetting extends Model
{
    public $table = "user_basic_settings";

    protected $guarded = [];

    public function language(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Language::class, 'user_id');
    }

}
