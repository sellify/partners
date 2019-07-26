<?php

namespace App\Providers;

use Anaseqal\NovaImport\NovaImport;
use App\Nova\App;
use App\Nova\Metrics\Partition\ShopsPerApp;
use App\Nova\Metrics\Trend\CommissionsPerDay;
use App\Nova\Metrics\Trend\EarningsPerDay;
use App\Nova\Metrics\Trend\EarningsPerPayout;
use App\Nova\Metrics\Trend\ShopsPerDay;
use App\Nova\Metrics\Trend\UsersPerDay;
use App\Nova\Metrics\Value\NewCommissions;
use App\Nova\Metrics\Value\NewUsers;
use Christophrumpel\NovaNotifications\NovaNotifications;
use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\NovaApplicationServiceProvider;
use Llaski\NovaScheduledJobs\NovaScheduledJobsTool;
use Spatie\BackupTool\BackupTool;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::style('nova-custom-css', public_path('css/nova-custom-css.css'));
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user ? true : false;
        });
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            (new EarningsPerDay())->width('1/2')->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new EarningsPerPayout())->width('1/2')->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new CommissionsPerDay())->width('1/2'),
            new NewCommissions(),
            (new ShopsPerDay())->width('1/2'),
            (new ShopsPerApp())->width('1/2'),
            (new UsersPerDay())->width('1/2')->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new NewUsers())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            (new NovaImport())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new NovaNotifications())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new BackupTool())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
            (new NovaScheduledJobsTool())->canSee(function ($request) {
                return $request->user()->isAdmin();
            }),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
