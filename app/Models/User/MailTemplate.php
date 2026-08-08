<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailTemplate extends Model
{
    use HasFactory;
    protected $table = 'user_mail_templates';
    protected $fillable = ['user_id','mail_type','mail_subject', 'mail_body'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
