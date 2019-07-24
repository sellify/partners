<?php

namespace App\Nova;

use App\Nova\Actions\ImportEarnings;
use App\Nova\Metrics\Trend\EarningsPerDay;
use App\Nova\Metrics\Trend\EarningsPerPayout;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Earning extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Earning::class;

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
    public static $subtitle = 'app.name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'charge_type',
        'amount',
        'category',
        'theme_name',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'shop' => [
            'shopify_domain',
        ],
        'app' => [
            'name',
            'slug',
        ],
    ];

    /**
     * Determine if relations should be searched globally.
     *
     * @var array
     */
    public static $searchRelationsGlobally = false;

    /**
     * Indicates if the resoruce should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = true;

    /**
     * Order by
     * @var array
     */
    public static $orderBy = [
        'charge_created_at' => 'desc',
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

            BelongsTo::make('App', 'app', \App\Nova\App::class)
                     ->searchable(),

            BelongsTo::make('Shop', 'shop', Shop::class)
                     ->searchable(),

            Number::make('Amount')
                  ->rules(['required', 'numeric'])
                  ->displayUsing(function ($price) {
                      return '$' . number_format($price / 100, 2);
                  })
                  ->sortable(),

            Text::make('Charge Type')
                ->sortable()
                ->hideFromIndex(),

            Text::make('Category')
                ->sortable()
                ->hideFromIndex(),

            Text::make('Theme Name')
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Start Date')
                ->format('MMM, DD YYYY')
                ->hideFromIndex(),

            DateTime::make('End Date')
                ->format('MMM, DD YYYY')
                ->hideFromIndex(),

            DateTime::make('Charged At', 'charge_created_at')
                    ->format('MMM, DD YYYY hh:mm A'),

            DateTime::make('Payout At', 'payout_date')
                    ->format('MMM, DD YYYY')
                    ->sortable(),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex(),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex(),

            HasMany::make('Commissions'),
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
            (new EarningsPerDay())->canSee(function ($request) {
                return $request->user()->isAdmin();
            })->width('1/2'),
            (new EarningsPerPayout())->canSee(function ($request) {
                return $request->user()->isAdmin();
            })->width('1/2'),
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
            new \App\Nova\Filters\App(),
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
        return [
            new ImportEarnings(),
        ];
    }
}
