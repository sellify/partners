<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Payout extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Payout::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $subtitle = 'transaction_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'payment_method',
        'transaction_id',
        'notes',
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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->help('A title to make sense of what this Payout is for.')
                ->rules('required', 'max:254'),

            BelongsTo::make('User', 'user', User::class),

            Number::make('Amount', 'amount')
                  ->rules(['required', 'numeric'])
                  ->displayUsing(function ($price) {
                      return '$' . number_format($price / 100, 2);
                  })
                  ->withMeta([
                      'extraAttributes' => [
                          'placeholder' => 'Example: 1000',
                      ],
                  ])
                  ->help('The amount in cents. If you paid $10.00, put 1000 in the field.')
                  ->sortable(),

            Text::make('Payment Method')
                ->rules('required', 'max:254'),

            Text::make('Transaction ID')
                ->rules('required', 'max:255'),

            DateTime::make('Payout At')
                ->format('MMM, DD YYYY hh:mm A')
                ->rules('required'),

            Textarea::make('Notes')
                    ->rules('required')
                    ->hideFromIndex(),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex()
                    ->hideWhenUpdating()
                    ->hideWhenCreating(),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->hideWhenUpdating(),

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
