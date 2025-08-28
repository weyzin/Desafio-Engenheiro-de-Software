<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, Notifiable, CanResetPasswordTrait;

    protected $fillable = [
        'tenant_id','name','email','password','role',
        'created_by','updated_by','deleted_by','impersonated_by',
        'last_login_at','failed_logins','last_password_change_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'last_login_at' => 'datetime',
        'last_password_change_at' => 'datetime',
    ];
}
