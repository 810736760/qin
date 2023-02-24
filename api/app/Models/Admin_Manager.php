<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin_Manager extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'admin_manager';
    protected $fillable = [
        'nickname', 'password', 'power', 'co'
    ];


    // 关联用户的fb账户
    public function facebookAdAccounts()
    {
        return $this->belongsToMany(get_class($this), 'facebook_ad_accounts_user_map', 'uid', 'aid');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
