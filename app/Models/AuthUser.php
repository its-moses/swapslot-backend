<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AuthUser extends Authenticatable implements JWTSubject
{
    protected $table = 'authuserstbl';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function events()
    {
        return $this->hasMany(EventsTbl::class, 'user_id');
    }

    public function sentSwapRequests()
    {
        return $this->hasMany(SwapRequest::class, 'requester_user_id');
    }

    public function receivedSwapRequests()
    {
        return $this->hasMany(SwapRequest::class, 'receiver_user_id');
    }
}
