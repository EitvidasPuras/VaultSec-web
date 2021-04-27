<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PaymentCard
 *
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPaymentCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPaymentCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VaultPaymentCard query()
 * @mixin \Eloquent
 */
class VaultPaymentCard extends Model
{
    protected $fillable = [
        'user_id', 'card_number', 'expiration_mm', 'expiration_yy', 'type', 'cvv', 'pin',
        'ip_address', 'currently_shared', 'created_at_device', 'updated_at_device'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
