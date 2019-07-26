<?php

namespace App\Nova;

use App\Nova\Actions\PayCommission;
use App\Nova\Filters\PaidOrUnpaid;
use App\Nova\Metrics\Trend\CommissionsPerDay;
use App\Nova\Metrics\Value\NewCommissions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class Commission extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Commission::class;

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
    public static $subtitle = 'user.username';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'amount',
        'paid_at',
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
        'user' => [
            'name',
            'username',
            'email',
            'paypal_email',
            'user_type',
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
    public static $globallySearchable = false;

    /**
     * Order by
     * @var array
     */
    public static $orderBy = [
        'created_at' => 'desc',
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

            BelongsTo::make('Referrer', 'user', User::class)
                     ->searchable(),

            BelongsTo::make('Earning', 'earning', Earning::class)
            ->hideFromIndex(),

            BelongsTo::make('Shop', 'shop', Shop::class)
                ->searchable(),

            BelongsTo::make('App', 'app', App::class)
                     ->searchable(),

            BelongsTo::make('Payout', 'payout', Payout::class)
                     ->nullable()
                     ->hideFromIndex()
                     ->searchable(),

            Number::make('Amount')
                  ->rules(['required', 'numeric'])
                  ->displayUsing(function ($price) {
                      return '$' . number_format($price / 100, 2);
                  })
                  ->sortable(),

            DateTime::make('Paid At')
                    ->format('MMM, DD YYYY hh:mm A')
            ->nullable(),

            Boolean::make('Paid')->withMeta([
                'value' => $this->paid_at && $this->payout_id,
            ])->onlyOnIndex(),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A'),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
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
            (new NewCommissions())->width('1/2'),
            (new CommissionsPerDay())->width('1/2'),
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
            new PaidOrUnpaid(),
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
            (new PayCommission())->canSee(function ($request) {
                if ($request->user()->isAdmin()) {
                    $model = $request->findModelQuery()->first();

                    return !($model ? $model->paid_at : false);
                }

                return false;
            })
            ->canRun(function ($request, $model) {
                return $request->user()->isAdmin() && $model && !$model->paid_at;
            }),
        ];
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
        return $request->user()->isAdmin() ? $query : $query->where('user_id', $request->user()->id);
    }
}
