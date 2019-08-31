<?php

namespace App\Nova;

use App\Nova\Lenses\MostValuableShops;
use App\Nova\Metrics\Partition\ShopsPerApp;
use App\Nova\Metrics\Trend\ShopsPerDay;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Shop extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Shop::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'shopify_domain';

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
        'shopify_domain',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'user' => [
            'name',
            'username',
            'email',
            'paypal_email',
            'user_type',
        ],
        'app'  => [
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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()
              ->sortable(),

            Text::make('Shopify Domain')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: myshop.myshopify.com',
                    ],
                ])
                ->help('Shopify domain is a shop\'s permanent domain which has .myshopify.com at the end. For eg. myshop.myshopify.com')
                ->sortable()
                ->rules('required', 'max:254', function ($attribute, $value, $fail) {
                    if (!Str::endsWith($value, '.myshopify.com')) {
                        $fail('Only permanent domains are allowed.');
                    }
                }),

            BelongsTo::make('App', 'app', \App\Nova\App::class)
                     ->searchable(),

            BelongsTo::make('Referrer', 'user', User::class)
                     ->searchable(),

            DateTime::make('Last Charge At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->sortable()
                    ->hideFromIndex()
                    ->hideWhenUpdating()
                    ->hideWhenCreating(),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideWhenUpdating()
                    ->hideWhenCreating()
                    ->sortable(),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->hideWhenUpdating(),

            HasMany::make('Earnings'),
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
            (new ShopsPerApp())->width('1/2'),
            (new ShopsPerDay())->width('1/2'),
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
        return [
            new MostValuableShops(),
        ];
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

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param  \Illuminate\Database\Eloquent\Builder   $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $request->user()
                       ->isAdmin() ? $query : $query->where('user_id', $request->user()
            ->id);
    }
}
