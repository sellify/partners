<?php

namespace App\Nova\Metrics\Trend;

use App\Commission;
use App\Traits\Nova\CacheKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class CommissionsPerMonth extends Trend
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
        $model = Commission::query();

        if ($request->resourceId) {
            $model->where($this->resourceColumn, $request->resourceId);
        }

        if (!$request->user()->isAdmin()) {
            $model->whereUserId($request->user()->id);
        }

        $trend = $this->sumByMonths($request, $model, 'amount', 'created_at')
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
            6     => '6 Months',
            1     => '1 Month',
            2     => '2 Months',
            3     => '90 Months',
            12    => '12 Months',
            Carbon::createFromDate(1971, 1, 1)->diffInMonths(Carbon::now())    => 'All Time',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'commissions-per-month';
    }
}
