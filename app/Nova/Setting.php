<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

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
    public static $title = ['identifier', '.', 'name'];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'label',
        'description',
        'value',
        'placeholder',
        'identifier',
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
            Text::make('Namespace', 'identifier')->rules('required'),
            Text::make('Name')->rules(['required']),
            Text::make('Label')->rules(['required'])->hideFromIndex(),
            Text::make('Value')->hideFromIndex(),
            Textarea::make('Description')->hideFromIndex(),
            Select::make('Type')->options([
                'BOOLEAN'    => 'Boolean',
                'TEXT'       => 'Text',
                'TEXTAREA'   => 'Textarea',
                'NUMBER'     => 'Number',
                'JSON'       => 'JSON',
                'CODE'       => 'Code',
            ])->rules(['required']),
            Text::make('Placeholder')->hideFromIndex(),
            Code::make('metadata')->json()->hideFromIndex(),
            Boolean::make('Editable', 'is_editable')->hideFromIndex(),

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

            BelongsToMany::make('Users')->fields(function () {
                return [
                    Textarea::make('value'),
                ];
            })->searchable(),
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
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
