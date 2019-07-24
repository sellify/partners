<?php

namespace App\Nova;

use App\Nova\Metrics\Trend\CommissionsPerDay;
use App\Nova\Metrics\Trend\ShopsPerDay;
use App\Nova\Metrics\Trend\UsersPerDay;
use App\Nova\Metrics\Value\NewUsers;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use KABBOUCHI\NovaImpersonate\Impersonate;

class User extends Resource
{
    use ResourceCommon;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'username';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $subtitle = 'email';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'username',
        'email',
        'paypal_email',
        'user_type',
    ];

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
        $fields = [
            ID::make()->sortable(),

            Gravatar::make(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:254')
                ->hideFromIndex(),

            Text::make('Username')
                ->sortable()
                ->canSee(function ($request) {
                    return $request->user()->isAdmin() || $this->viewIs(['index', 'detail'], $request);
                })
                ->rules('required', 'max:254')
                ->creationRules('unique:users')
                ->updateRules('unique:users,username,{{resourceId}}'),

            Text::make('Email')
                ->sortable()
                ->readonly(function ($request) {
                    return !$request->user()->isAdmin();
                })
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->hideFromIndex(),

            Text::make('PayPal Email', 'paypal_email')
                ->sortable()
                ->rules('email', 'max:254')
                ->hideFromIndex(),

            Select::make('User type')
                  ->options($request->user()->isSuperAdmin() ? [
                      'affiliate' => 'Affiliate',
                      'admin'     => 'Admin',
                      'super'     => 'Super Admin',
                  ] : [
                      'affiliate' => 'Affiliate',
                  ])
                ->canSee(function ($request) {
                    return $request->user()->isAdmin() || $this->viewIs(['index', 'detail'], $request);
                })
                  ->hideFromIndex()
                  ->hideFromDetail(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            Number::make('Commission %', 'commission')
                  ->sortable()
                  ->max(100)
                  ->min(0)
                    ->withMeta(array_merge(
                        [
                        'extraAttributes' => [
                            'placeholder' => 'Example: 10',
                        ],
                    ],
                        $this->viewIs('form', $request) ? [
                            'value' => $this->commission ?? $request->user()->setting('user.commission'),
                        ] : []
                    ))
                  ->displayUsing(function ($commission) {
                      return $commission . '%';
                  })
                ->canSee(function ($request) {
                    return $request->user()->isAdmin() || $this->viewIs(['index', 'detail'], $request);
                }),

            Number::make('Minimum Payout')
                  ->rules(['required', 'numeric'])

                 ->withMeta(array_merge([
                     'extraAttributes' => [
                        'placeholder' => 'Example: 1000',
                     ],
                 ], $this->viewIs('form', $request) ? [
                     'value' => $this->minimum_payout ?? $request->user()->setting('user.minimum_payout'),
                 ] : []))
                 ->displayUsing(function ($price) {
                     return $price > 0 ? '$' . number_format($price / 100, 2) : 'N/A';
                 })
                ->canSee(function ($request) {
                    return $request->user()->isAdmin() || $this->viewIs(['index', 'detail'], $request);
                })
                 ->help('The amount in cents. If you paid $10.00, put 1000 in the field.')
                 ->sortable(),

            DateTime::make('Created At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideWhenUpdating()
                    ->hideWhenCreating(),

            DateTime::make('Updated At')
                    ->format('MMM, DD YYYY hh:mm A')
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->hideWhenUpdating(),
        ];

        if ($request->user()->isAdmin()) {
            $fields = array_merge($fields, [
                HasMany::make('Shops'),
                HasMany::make('Commissions'),
                HasMany::make('Payouts'),
                Impersonate::make($this),
                BelongsToMany::make('Settings')->fields(function () {
                    return [
                        Textarea::make('value'),
                    ];
                })
                ->searchable(),
            ]);
        }

        return $fields;
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
            (new NewUsers())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new UsersPerDay())->canSee(function ($request) {
                return $request->user()->isAdmin();
            })->width('1/2'),
            (new CommissionsPerDay())->resourceColumn('user_id')->width('1/2')->onlyOnDetail(),
            (new ShopsPerDay())->resourceColumn('user_id')->width('1/2')->onlyOnDetail(),
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
        return [
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
        return $request->user()->isAdmin() ? $query : $query->where('id', $request->user()->id);
    }
}
