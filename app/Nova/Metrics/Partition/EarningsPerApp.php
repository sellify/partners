<?php

namespace App\Nova\Metrics\Partition;

use App\App;
use App\Earning;
use App\Traits\Nova\CacheKey;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class EarningsPerApp extends Partition
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
        $model = Earning::query();
        $column = 'earnings.amount';

        if ($request->resourceId) {
            $model->where($this->resourceColumn, $request->resourceId);
        }

        if (!$request->user()->isAdmin()) {
            $model->join('commissions', 'commissions.earning_id', '=', 'earnings.id')
                  ->where('commissions.user_id', $request->user()->id);

            $column = 'commissions.amount';
        }

        $apps = (new App())->getApps();

        $partition = $this->sum($request, $model, $column, 'earnings.app_id');

        $partition->value = collect($apps)->mapWithKeys(function ($app) use ($partition) {
            return [$app['name'] => floor(($partition->value[$app['id']] ?? 0) / 100)];
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
        return 'earnings-per-app';
    }
}
