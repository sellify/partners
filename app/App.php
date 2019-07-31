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
}
