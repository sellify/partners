<?php

namespace App\Nova;

use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceDetailRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return parent::relatableQuery($request, $query);
    }

    /**
     * Check the current view.
     *
     * @param  string                                  $view
     * @param  $request
     *
     * @retrun bool
     */
    public function viewIs($view, $request)
    {
        $response = false;
        $classesByView = [
            'create' => CreateResourceRequest::class,
            'detail' => ResourceDetailRequest::class,
            'index'  => ResourceIndexRequest::class,
            'update' => UpdateResourceRequest::class,
            'form'   => [
                CreateResourceRequest::class,
                UpdateResourceRequest::class,
            ],
        ];

        $classes = $classesByView[$view] ?? [];

        $classes = is_array($classes) ? $classes : [$classes];

        foreach ($classes as $class) {
            if ($request instanceof $class) {
                $response = true;
                break;
            }
        }

        if (!$response) {
            if ($request instanceof NovaRequest) {
                if ($view === 'form') {
                    $response = $request->isCreateOrAttachRequest() || $request->isUpdateOrUpdateAttachedRequest();
                } elseif ($view === 'create') {
                    $response = $request->isCreateOrAttachRequest();
                } elseif ($view === 'update') {
                    $response = $request->isUpdateOrUpdateAttachedRequest();
                }
            }
        }

        return $response;
    }
}
