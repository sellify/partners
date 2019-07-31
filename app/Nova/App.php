<?php

namespace App\Nova;

use App\Nova\Metrics\Partition\EarningsPerApp;
use App\Nova\Metrics\Partition\ShopsPerApp;
use App\Nova\Metrics\Trend\CommissionsPerDay;
use App\Nova\Metrics\Trend\EarningsPerDay;
use App\Nova\Metrics\Trend\EarningsPerPayout;
use App\Nova\Metrics\Trend\ShopsPerDay;
use Fourstacks\NovaRepeatableFields\Repeater;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class App extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\App::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $subtitle = 'appstore_url';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'slug',
        'url',
        'appstore_url',
        'price',
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

            Text::make('Name')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: My awesome app',
                    ],
                ])
                ->help('The name should be identical to one on the App Store.')
                ->sortable()
                ->rules('required', 'max:254'),

            Text::make('Slug')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: my-awesome-app',
                    ],
                ])
                ->help('The should be the unique slug and must be same on the App Store. If your app store url is https://apps.shopify.com/my-awesome-app then \'my-awesome-app\' is the slug.')
                ->sortable()
                ->rules('required', 'max:254')
                ->hideFromIndex(),

            Text::make('Affiliate URL', 'url')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: https://my-app.com/auth',
                    ],
                ])
                ->help('The landing url where you catch the referrer from url. Do not add your username here, it will be added automatically.')
                ->sortable()
                ->rules('required')
                ->displayUsing(function ($url) use ($request) {
                    return $url ? (addQueryParam($url, 'ref', $request->user()->username)) : '-';
                }),

            Text::make('AppStore URL', 'appstore_url')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: https://apps.shopify.com/my-awesome-app',
                    ],
                ])
                ->help('The link of your app on app store.')
                ->sortable()
                ->rules('required')
                ->hideFromIndex(),

            Number::make('Price')
                ->withMeta([
                    'extraAttributes' => [
                        'placeholder' => 'Example: 1000',
                    ],
                ])
                ->help('The base price of your app in cents. If you app costs $10.00, put 1000 in the field.')
                  ->rules(['required', 'numeric'])
                  ->displayUsing(function ($price) {
                      return $price > 0 ? '$' . number_format($price / 100, 2) : '-';
                  })
                  ->sortable()
                ->hideFromIndex(),

            Boolean::make('Active'),

            Repeater::make('Other Names')
                ->help('If the app was used to have different name in past. Shopify might give the earnings details with those names for the payouts during that period.')
                ->addField([
                'label'       => 'Name',
                'type'        => 'text',
                'placeholder' => 'My awesome App',
            ])
                ->addButtonText('Add another name')
            ->initialRows(1)
            ->hideFromIndex(),

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

        $fields[] = HasMany::make('Shops', 'shops', '\App\Nova\Shop');

        if ($request->user()->isAdmin()) {
            $fields[] = HasMany::make('Earnings', 'earnings', '\App\Nova\Earning');
        }

        $fields[] = HasMany::make('Commissions', 'commissions', '\App\Nova\Commission');

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
            (new ShopsPerApp())->width('1/2'),
            (new EarningsPerApp())->width('1/2'),
            (new EarningsPerDay())->canSee(function ($request) {
                return $request->user()->isAdmin();
            })->resourceColumn('app_id')->width('1/2')->onlyOnDetail(),
            (new EarningsPerPayout())->canSee(function ($request) {
                return $request->user()->isAdmin();
            })->resourceColumn('app_id')->width('1/2')->onlyOnDetail(),
            (new CommissionsPerDay())->resourceColumn('app_id')->width('1/2')->onlyOnDetail(),
            (new ShopsPerDay())->resourceColumn('app_id')->width('1/2')->onlyOnDetail(),
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
