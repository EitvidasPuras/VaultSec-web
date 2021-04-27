<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VaultPassword
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property string|null $url
 * @property string|null $login
 * @property string $password
 * @property string $category
 * @property string $color
 * @property string $ip_address
 * @property int $currently_shared
 * @property string $created_at_device
 * @property string $updated_at_device
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword query()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereCreatedAtDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereCurrentlyShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereUpdatedAtDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPassword whereUserId($value)
 * @mixin \Eloquent
 */
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
