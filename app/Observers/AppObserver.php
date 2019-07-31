<?php

namespace App\Observers;

use App\App;
use Illuminate\Support\Facades\Cache;

class AppObserver
{
    /**
     * Handle the app "created" event.
     *
     * @param  \App\App  $app
     * @return void
     */
    public function created(App $app)
    {
        $this->clearCache();
    }

    /**
     * Handle the app "updated" event.
     *
     * @param  \App\App  $app
     * @return void
     */
    public function updated(App $app)
    {
        $this->clearCache();
    }

    /**
     * Handle the app "deleted" event.
     *
     * @param  \App\App  $app
     * @return void
     */
    public function deleted(App $app)
    {
        $this->clearCache();
    }

    /**
     * Handle the app "restored" event.
     *
     * @param  \App\App  $app
     * @return void
     */
    public function restored(App $app)
    {
        $this->clearCache();
    }

    /**
     * Handle the app "force deleted" event.
     *
     * @param  \App\App  $app
     * @return void
     */
    public function forceDeleted(App $app)
    {
        $this->clearCache();
    }

    /**
     * Clear cache
     */
    private function clearCache()
    {
        Cache::forget(App::$cacheKey);
    }
}
