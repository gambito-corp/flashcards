<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
//        $schedule->command('subscriptions:check-status')->dailyAt('00:01');
//        $schedule->command('subscriptions:send-renewal-reminders')->dailyAt('00:01');
//        $schedule->command('subscriptions:send-renewal-reminders')->dailyAt('00:01');
//        $schedule->command('trial:send-expiration-reminders')->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
