<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Relations\HasMany\Shops as HasManyShops;
use App\Traits\Relations\HasMany\Earnings as HasManyEarnings;
use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class App extends Model
{
    use SoftDeletes, HasManyShops, HasManyEarnings, HasManyCommissions;

    /**
     * Guarded columns
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Cache key
     *
     * @var string
     */
    public static $cacheKey = 'apps';

    /**
     * Cast columns
     * @var array
     */
    protected $casts = [
        'other_names' => 'array',
    ];

    /**
     * Apps
     *
     * @param boolean $fresh
     *
     * @return array
     */
    public function getApps($fresh = false)
    {
        if ($fresh) {
            \Cache::forget(self::$cacheKey);
        }

        $formattedApps = Cache::get(self::$cacheKey, function () {
            $apps = self::select([
                'id',
                'name',
                'slug',
                'active',
                'other_names',
            ])->get()->keyBy('id')->toArray();

            Cache::add(self::$cacheKey, $apps, now()->addDays(7));

            return $apps;
        });

        return $formattedApps;
    }

    /**
     * Get single app
     *
     * @param      $id
     * @param null $default
     *
     * @return mixed
     */
    public function app($id, $default = null, $fresh = false)
    {
        return Arr::get($this->getApps($fresh), $id, $default);
    }

    /**
     * Apps key by name
     *
     * @param bool $fresh
     *
     * @return array
     */
    public function appsByNames($fresh = false)
    {
        $apps = $this->getApps($fresh);
        $appsByName = [];
        collect($apps)->each(function ($app) use (&$appsByName) {
            $appsByName[$app['name']] = $app['id'];
            $appsByName[strtolower($app['name'])] = $app['id'];
            if ($app['other_names']) {
                foreach ($app['other_names'] as $name) {
                    $appsByName[$name['name']] = $app['id'];
                    $appsByName[strtolower($name['name'])] = $app['id'];
                }
            }
        });

        return $appsByName;
    }
}
