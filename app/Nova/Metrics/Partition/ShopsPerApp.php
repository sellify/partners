<?php

namespace App\Nova\Metrics\Partition;

use App\App;
use App\Shop;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class ShopsPerApp extends Partition
{
    use CacheKey;

    protected $resourceColumn = 'app_id';

    /**
     * Set resource column
     *
     * @param $column
     *
     * @return $this
     */
    public function resourceColumn($column)
    {
        $this->resourceColumn = $column;

        return $this;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $model = Shop::query();

        if ($request->resourceId) {
            $model->where($this->resourceColumn, $request->resourceId);
        }

        if (!$request->user()->isAdmin()) {
            $model->where('user_id', $request->user()->id);
        }

        $apps = (new App())->getApps();

        $partition = $this->count($request, $model, 'app_id', 'app_id');

        $partition->value = collect($apps)->mapWithKeys(function ($app) use ($partition) {
            return [$app['name'] => $partition->value[$app['id']] ?? 0];
        })->toArray();

        return $partition;
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'shops-per-app';
    }
}
