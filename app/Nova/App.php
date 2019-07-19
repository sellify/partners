<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class App extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\App';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
