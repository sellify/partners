<?php

namespace App\Nova\Metrics\Trend;

use App\Earning;
use App\Traits\Nova\CacheKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class EarningsPerMonth extends Trend
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
            return $query->where('payout_date', '>=', Carbon::now()->subMonths($request->get('range')));
        })
                            ->when($request->resourceId > 0, function ($query) use ($request) {
                                return $query->where($this->resourceColumn, $request->resourceId);
                            })
                            ->groupBy(DB::raw("DATE_FORMAT(charge_created_at, '%Y-%m')"))
                            ->select(\DB::raw('sum(amount) as amount'), 'charge_created_at')
                            ->orderBy('charge_created_at', 'asc')
                            ->get();

        $trend = [];

        foreach ($earnings as $earning) {
            $trend[$earning->charge_created_at->format('M, Y')] = $earning->amount / 100;
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
            6                                                               => '6 Months',
            1                                                               => '1 Month',
            2                                                               => '2 Months',
            3                                                               => '90 Months',
            12                                                              => '12 Months',
            Carbon::createFromDate(1971, 1, 1)->diffInMonths(Carbon::now()) => 'All Time',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'earnings-per-month';
    }
}
