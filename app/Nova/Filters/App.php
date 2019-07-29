<?php

namespace App\Nova\Filters;

use App\Traits\Nova\Filters\Table;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class App extends BooleanFilter
{
    use Table;

    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Select App(s)';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $selectedApps = array_keys(array_filter($value, function ($app) {
            return $app;
        }));

        if ($selectedApps) {
            $query = $query->whereIn(($this->table ? $this->table . '.' : '') . 'app_id', $selectedApps);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return \App\App::select('id', 'name')->get()->pluck('id', 'name')->toArray();
    }
}
