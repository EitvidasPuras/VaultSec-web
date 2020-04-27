<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'is_admin',
        'ip_address', 'country', 'city', 'postal_code',
        'latitude', 'longitude'];

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

    public function oAuthAccessToken()
    {
        return $this->hasOne(OauthAccessToken::class);
    }

    public function loginSession()
    {
        return $this->hasMany(LoginSession::class)
            ->orderBy('updated_at', 'asc');
    }

    public function vaultPassword()
    {
        return $this->hasMany(VaultPassword::class);
    }

    public function vaultNote()
    {
        return $this->hasMany(VaultNote::class);
    }
}
