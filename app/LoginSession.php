<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'country', 'city', 'postal_code',
        'latitude', 'longitude', 'currently_active'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
