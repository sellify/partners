<?php

namespace App\Nova\Metrics\Trend;

use App\Earning;
use App\Traits\Nova\CacheKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class EarningsPerPayout extends Trend
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
        //
        $earnings = Earning::when($request->get('range') > 0, function ($query) use ($request) {
            return $query->where('payout_date', '>=', Carbon::now()->subDays($request->get('range')));
        })
                            ->when($request->resourceId > 0, function ($query) use ($request) {
                                return $query->where($this->resourceColumn, $request->resourceId);
                            })
                            ->groupBy('payout_date')
                            ->select(\DB::raw('sum(amount) as amount'), 'payout_date')
                            ->orderBy('payout_date', 'asc')
                            ->get();

        $trend = [];
        foreach ($earnings as $earning) {
            $trend[$earning->payout_date->format('M d, Y')] = $earning->amount / 100;
        }

        return (new TrendResult())->dollars()->trend($trend)->showLatestValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            180   => '180 Days',
            30    => '30 Days',
            60    => '60 Days',
            90    => '90 Days',
            365   => '365 Days',
            -1    => 'All Time',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'earnings-per-payout';
    }
}
