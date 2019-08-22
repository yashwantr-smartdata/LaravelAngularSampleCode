<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','email','email_verified_at','password','mobile_number','mobile_verified','user_role','is_active','social_auth_token','social_id','social_id_token','photo_url','provider', 'language', 'address', 'gst_id', 'gst_no', 'no_gst_refund', 'country_id', 'login_attempts', 'payment_method', 'membership', 'company_name', 'notify_email', 'notify_mobile', 'steps', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function customer_detail(){
        return $this->hasOne('App\CustomerCardDetail','user_id','id');
    }
}


