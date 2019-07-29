<?php

namespace App\Nova\Metrics\Trend;

use App\Shop;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class ShopsPerDay extends Trend
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

        return $this->countByDays($request, $model)->showLatestValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30    => '30 Days',
            60    => '60 Days',
            90    => '90 Days',
            180   => '180 Days',
            365   => '365 Days',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'shops-per-day';
    }
}
