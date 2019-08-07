<?php

namespace App\Providers;

use App\Events\EarningAdded;
use App\Events\ShopInstalledApp;
use App\Listeners\CalculateCommissions;
use App\Listeners\NotifyReferrer;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EarningAdded::class => [
            CalculateCommissions::class,
        ],
        ShopInstalledApp::class => [
            NotifyReferrer::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
