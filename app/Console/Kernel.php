<?php

namespace App\Console;

use App\Jobs\CheckPayPalPendingPayoutsStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:clean')->daily()->at('02:00');
        $schedule->command('backup:run')->daily()->at('02:10');

        // Check and update PayPal batch payout status
        $schedule->job(new CheckPayPalPendingPayoutsStatus())->dailyAt('03:00')->withoutOverlapping();

        // Prune telescope data
        $schedule->command('telescope:prune --hours=72')->dailyAt('03:05');

        // Horizon snapshot
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        // Fetch affiliates in last n days
        $schedule->command('fetch:affiliates_and_shops')->dailyAt('03:07');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
