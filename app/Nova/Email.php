<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class Email extends Resource
{
    use ResourceCommon;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Email::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'subject';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $subtitle = 'event';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'subject',
        'event',
        'content',
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
        return [
            ID::make()->sortable(),

            Text::make('Subject'),

            Select::make('Event')
                ->options([
                    'installed' => 'App Installed',
                ])
                ->displayUsingLabels()
            ->sortable(),

            Code::make('Content')
            ->hideFromIndex(),

            Boolean::make('Active')
            ->sortable(),

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
