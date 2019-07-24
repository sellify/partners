<?php

namespace App\Nova\Metrics\Trend;

use App\Commission;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class CommissionsPerDay extends Trend
{
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
        $model = Commission::query();

        if ($request->resourceId) {
            $model->where($this->resourceColumn, $request->resourceId);
        }

        if (!$request->user()->isAdmin()) {
            $model->whereUserId($request->user()->id);
        }

        $trend = $this->sumByDays($request, $model, 'amount', 'created_at')
                    ->dollars()
                    ->showLatestValue();

        if ($trend) {
            $trend->value /= 100;

            if ($trend->trend) {
                foreach ($trend->trend as $duration => $value) {
                    $trend->trend[$duration] = $value / 100;
                }
            }
        }

        return $trend;
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
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        //return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'commissions-per-day';
    }
}
