<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VaultNote
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property string $text
 * @property string $color
 * @property int $font_size
 * @property string $ip_address
 * @property int $currently_shared
 * @property string $created_at_device
 * @property string $updated_at_device
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereCreatedAtDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereCurrentlyShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereFontSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereUpdatedAtDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VaultNote whereUserId($value)
 * @mixin \Eloquent
 */
class VaultNote extends Model
{
    protected $fillable = [
        'user_id', 'title', 'text', 'color', 'ip_address', 'font_size', 'created_at_device', 'updated_at_device'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
