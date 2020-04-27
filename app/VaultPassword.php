<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultPassword extends Model
{
    protected $fillable = [
        'user_id', 'title', 'website_name', 'login', 'password',
        'category', 'ip_address', 'currently_shared'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
