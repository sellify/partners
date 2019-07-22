<?php

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class Setting extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Setting::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $subtitle = 'title';

    /**
     * Indicates if the resoruce should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'minimum_payout',
        'commission',
        'payout_date_1',
        'payout_date_2',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Title'),

            Number::make('Commission %', 'commission')
                  ->sortable()
                  ->max(100)
                  ->min(0)
                  ->displayUsing(function ($commission) {
                      return $commission . '%';
                  }),

            Number::make('Minimum Payout')
                  ->rules(['required', 'numeric'])
                  ->displayUsing(function ($price) {
                      return $price > 0 ? '$' . number_format($price / 100, 2) : 'N/A';
                  })->sortable(),

            Select::make('First Payout On', 'payout_date_1')
                  ->options(array_combine(range(1, 31), range(1, 31)))
                  ->displayUsing(function ($date) {
                      return Carbon::now()->setDay($date)->isoFormat('Do');
                  }),

            Select::make('Second Payout On', 'payout_date_2')
                  ->options(array_combine(range(1, 31), range(1, 31)))
            ->displayUsing(function ($date) {
                return Carbon::now()->setDay($date)->isoFormat('Do');
            }),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideWhenCreating()
                    ->hideWhenUpdating()
                    ->hideFromIndex(),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideWhenCreating()
                    ->hideWhenUpdating()
                    ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
