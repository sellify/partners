<?php

namespace App\Nova\Metrics\Value;

use App\Earning;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class NewEarnings extends Value
{
    use CacheKey;

    public $name = 'Earnings by range';

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $value = $this->sum($request, Earning::class, 'amount', 'charge_created_at')
            ->dollars('$');

        $value->previous /= 100;
        $value->value /= 100;

        return $value;
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            15    => '15 Days',
            30    => '30 Days',
            60    => '60 Days',
            90    => '90 Days',
            180   => '180 Days',
            365   => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-earnings';
    }
}
