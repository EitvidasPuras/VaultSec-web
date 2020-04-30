<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultFile extends Model
{
    protected $fillable = [
        'user_id', 'file_name', 'stored_file_name', 'file_extension',
        'file_size', 'file_size_v', 'base64', 'ip_address', 'currently_shared'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
