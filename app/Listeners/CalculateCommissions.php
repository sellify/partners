<?php

namespace App\Listeners;

use App\Earning;
use App\Events\EarningAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

class CalculateCommissions implements ShouldQueue
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
     * @param  EarningAdded  $event
     * @return void
     */
    public function handle(EarningAdded $event)
    {
        $earnings = Earning::with('shop.user:id,commission')
                          ->whereHas('shop', function (Builder $query) {
                              $query->whereNotNull('shops.user_id');
                          })
                          ->whereDoesntHave('commissions')
                          ->get();

        foreach ($earnings as $earning) {
            $commission = $earning->amount * ($earning->shop->user->commission / 100);

            if ($commission > 0) {
                $earning->commissions()->create([
                    'user_id'    => $earning->shop->user_id,
                    'app_id'     => $earning->app_id,
                    'shop_id'    => $earning->shop->id,
                    'paid_at'    => null,
                    'amount'     => $commission,
                    'created_at' => $earning->charge_created_at,
                ]);
            }
        }
    }
}
