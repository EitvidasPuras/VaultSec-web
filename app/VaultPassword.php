<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultPassword extends Model
{
    protected $fillable = [
        'user_id', 'title', 'url', 'login', 'password',
        'category', 'color', 'ip_address', 'currently_shared', 'created_at_device', 'updated_at_device'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
