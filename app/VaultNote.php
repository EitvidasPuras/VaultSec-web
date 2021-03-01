<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultNote extends Model
{
    protected $fillable = [
        'user_id', 'title', 'text', 'color', 'font_size', 'created_at_device', 'updated_at_device'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
