<?php

namespace App\Listeners;

use App\Events\ShopInstalledApp;
use App\Notifications\SuccessfulReferral;

class NotifyReferrer
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ShopInstalledApp  $event
     * @return void
     */
    public function handle(ShopInstalledApp $event)
    {
        if ($event->shop->user && $event->shop->user->setting('user.successful_referral_email')) {
            $event->shop->user->notify(new SuccessfulReferral($event->shop));
        }
    }
}
