<?php

namespace App;

use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use App\Traits\Relations\HasMany\Payouts as HasManyPayouts;
use App\Traits\Relations\HasMany\Shops as HasManyShops;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,
        HasApiTokens,
        HasManyShops,
        HasManyCommissions,
        HasManyPayouts;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'paypal_email',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isSuperAdmin()
    {
        return in_array($this->user_type, ['super']);
    }

    public function isAdmin()
    {
        return in_array($this->user_type, ['admin', 'super']);
    }

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->isAdmin();
    }

    /**
     * @return bool
     */
    public function canBeImpersonated()
    {
        return !$this->isSuperAdmin() && $this->id !== request()->user()->id;
    }
}
