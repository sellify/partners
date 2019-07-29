<?php

namespace App\Traits\Nova;

use Laravel\Nova\Http\Requests\NovaRequest;

trait CacheKey
{
    /**
     * Get the appropriate cache key for the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return string
     */
    protected function getCacheKey(NovaRequest $request)
    {
        $key = sprintf(
            'nova.metric.%s.%s.%s.%s.%s.%s',
            $this->uriKey(),
            $request->input('range', 'no-range'),
            $request->input('timezone', 'no-timezone'),
            $request->input('twelveHourTime', 'no-12-hour-time'),
            $request->user()->id,
            ($this->resourceColumn ?? '0') . ($request->resourceId ?? '0')
        );

        return $key;
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        $time = now()->addMinutes(5);

        return config('app.env') !== 'production' ? null : $time;
    }
}
