<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\LoginSession
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $country
 * @property string|null $city
 * @property string|null $postal_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $currently_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCurrentlyActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereUserId($value)
 * @mixin \Eloquent
 */
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
