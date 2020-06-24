<?php

namespace App\Nova\Metrics\Trend;

use App\Earning;
use App\Traits\Nova\CacheKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class EarningsPerMonthToday extends Trend
{
    use CacheKey;

    protected $resourceColumn = 'app_id';
    protected $rangeDate = null;

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
        $range = $request->get('range');
        $this->rangeDate = Carbon::now()->subMonths($range)->startOfMonth();
        $earnings = Earning::when($range > 0, function ($query) use ($request, $range) {
            return $query->where('charge_created_at', '>=', $this->rangeDate);
        })
            ->when($request->resourceId > 0, function ($query) use ($request) {
                return $query->where($this->resourceColumn, $request->resourceId);
            })
            ->where(DB::raw("DATE_FORMAT(charge_created_at, '%d')"), '<=', Carbon::today()->day)
            ->groupBy(DB::raw("DATE_FORMAT(charge_created_at, '%Y-%m')"))
            ->select(\DB::raw('sum(amount) as amount'), 'charge_created_at')
            ->orderBy('charge_created_at', 'asc')
            ->get();

        $trend = [];

        foreach ($earnings as $earning) {
            $trend[Carbon::today()->startOfMonth()->day . ' ' . $earning->charge_created_at->format('M') . '-' . Carbon::today()->day . ' ' . $earning->charge_created_at->format('M, Y')] = $earning->amount / 100;
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
            3                                                               => '3 Months',
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
        return 'earnings-per-month-today';
    }

    /**
     * @return string
     */
    public function name(): string
    {
        $day = $this->rangeDate ? $this->rangeDate->format('d') : Carbon::today()->format('d');
        $locale = 'en_US';
        $nf = new \NumberFormatter($locale, \NumberFormatter::ORDINAL);
        $day = $nf->format($day);

        return 'Earnings Per Month till ' . $day;
    }
}
