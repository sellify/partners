<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Relations\HasMany\Shops as HasManyShops;
use App\Traits\Relations\HasMany\Earnings as HasManyEarnings;
use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class App extends Model
{
    use HasManyShops, HasManyEarnings, HasManyCommissions;

    protected $guarded = [
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
        $cacheKey = 'apps';

        if ($fresh) {
            \Cache::forget($cacheKey);
        }

        $formattedApps = Cache::get($cacheKey, function () use ($cacheKey) {
            $apps = self::select([
                'id',
                'name',
                'slug',
            ])->get()->keyBy('id')->toArray();

            Cache::add($cacheKey, $apps, now()->addDays(7));

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
