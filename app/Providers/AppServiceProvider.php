<?php

namespace App\Providers;

use App\App;
use App\Observers\AppObserver;
use App\Observers\SettingObserver;
use App\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerObservers();
    }

    /**
     * Register Model observers
     */
    private function registerObservers()
    {
        App::observe(AppObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
