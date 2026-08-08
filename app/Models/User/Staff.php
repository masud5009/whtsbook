<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roleInfo(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role');
    }
}
