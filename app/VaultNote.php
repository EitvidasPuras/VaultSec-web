<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultNote extends Model
{
    protected $fillable = [
        'user_id', 'title', 'text', 'color', 'font_size', 'ip_address', 'currently_shared'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
