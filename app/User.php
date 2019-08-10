<?php

namespace App;

use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use App\Traits\Relations\HasMany\Shops as HasManyShops;
use App\Traits\Relations\BelongsToMany\Settings as BelongsToManySettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable,
        HasApiTokens,
        SoftDeletes,
        HasManyShops,
        HasManyCommissions,
        BelongsToManySettings;

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
        'commission',
        'minimum_payout',
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

    /**
     * User settings
     * @param string $keys
     *
     * @return array
     */
    public function getSettings($keys = '*', $fresh = false)
    {
        $cacheKey = 'user_' . ($this->id ?? 0) . '_setting';

        if ($fresh || $this->id) {
            \Cache::forget($cacheKey);
        }

        $formattedSettings = Cache::get($cacheKey, function () use ($cacheKey, $keys) {
            $settings = Setting::distinct()->leftJoin('user_setting', function (JoinClause $join) {
                $join->on('settings.id', '=', 'user_setting.setting_id');
                $join->on('user_setting.user_id', '=', \DB::raw($this->id ?? 0));
            });

            if ($keys != '*') {
                $keys = !is_array($keys) ? [$keys] : $keys;
                $settings = $settings->whereIn('settings.name', $keys);
            }

            $settings->select([
                'settings.id',
                'user_setting.id as user_setting_id',
                'settings.name',
                'settings.value as default_value',
                'settings.type',
                'settings.identifier',
                'user_setting.value',
            ])->get()->each(function ($setting) use (&$formattedSettings) {
                $formattedSettings[$setting['identifier']][$setting['name']] = app_setting(['custom' => $setting->toArray()], 'custom', true);
            });

            Cache::add($cacheKey, $formattedSettings, now()->addDays(7));

            return $formattedSettings;
        });

        return $formattedSettings;
    }

    /**
     * Get single setting
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function setting($key, $default = null, $fresh = false)
    {
        return Arr::get($this->getSettings('*', $fresh), $key, $default);
    }
}
