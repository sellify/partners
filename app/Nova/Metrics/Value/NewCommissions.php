<?php

namespace App\Nova\Metrics\Value;

use App\Commission;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class NewCommissions extends Value
{
    use CacheKey;

    public $name = 'Commissions by range';

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

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

        $value = $this->sum($request, $model, 'amount', 'created_at')
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
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-commissions';
    }
}
