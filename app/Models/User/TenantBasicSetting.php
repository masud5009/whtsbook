<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantBasicSetting extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function language(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Language::class, 'user_id');
    }
}
