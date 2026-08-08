<?php

namespace App\Models;

use App\Models\User\Language;
use App\Models\User\Role;
use App\Models\User\RoomReview;
use App\Models\User\Staff;
use App\Models\User\UserQrCode;
use App\Models\User\BasicSetting;
use App\Models\User\MailTemplate;
use App\Models\User\UserPermission;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'photo',
        'username',
        'password',
        'phone',
        'company_name',
        'city',
        'state',
        'address',
        'country',
        'status',
        'featured',
        'verification_link',
        'email_verified',
        'online_status',
        'pass_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function mail_templates(): HasMany
    {
        return $this->hasMany(MailTemplate::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id');
    }

    public function basic_setting(): HasOne
    {
        return $this->hasOne(BasicSetting::class, 'user_id');
    }

    public function qr_codes(): HasMany
    {
        return $this->hasMany(UserQrCode::class, 'user_id');
    }
    public function languages()
    {
        return $this->hasMany(Language::class, 'user_id');
    }

    public function online_gateways(): HasMany
    {
        return $this->hasMany(\App\Models\User\PaymentGateway::class, 'user_id');
    }
    public function offline_gateways(): HasMany
    {
        return $this->hasMany(\App\Models\User\OfflineGateway::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\User\UserTicket');
    }
    public function conversations()
    {
        return $this->hasMany('App\Models\User\UserConversation');
    }

    public function rooms()
    {
        return $this->hasMany('App\Models\User\Room');
    }

    public function room_amenities()
    {
        return $this->hasMany('App\Models\User\RoomAmenity');
    }
    public function roomBookingCoupons()
    {
        return $this->hasMany('App\Models\User\Coupon');
    }

    public function bookHotelRoom()
    {
        return $this->hasMany('App\Models\User\RoomBooking');
    }

    public function giveReviewForRoom()
    {
        return $this->hasMany('App\Models\User\RoomReview');
    }


    public function permissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id');
    }

    public function room_reviews()
    {
        return $this->hasMany(RoomReview::class, 'user_id');
    }

    public function aiUsage()
    {
        return $this->hasOne(AiUsageToken::class, 'user_id');
    }

    public function staffRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'user_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'user_id');
    }
}
