<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\App;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;

class MostValuableShops extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->select(self::columns())
                  ->join('earnings', 'earnings.shop_id', '=', 'shops.id')
                  ->where('earnings.app_id', DB::raw('shops.app_id'))
                  ->orderBy('revenue', 'desc')
                  ->groupBy('shops.id', 'shops.app_id')
        ));
    }

    /**
     * Get the columns that should be selected.
     *
     * @return array
     */
    protected static function columns()
    {
        return [
            'shops.id',
            'shops.shopify_domain',
            'shops.app_id',
            'shops.user_id',
            DB::raw('sum(earnings.amount) as revenue'),
        ];
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Shopify Domain'),
            BelongsTo::make('App', 'app', \App\Nova\App::class),
            BelongsTo::make('Referrer', 'user', \App\Nova\User::class),
            Number::make('Revenue')
                  ->displayUsing(function ($price) {
                      return '$' . number_format($price / 100, 2);
                  }),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            (new App())->table('shops'),
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'most-valuable-shops';
    }
}
