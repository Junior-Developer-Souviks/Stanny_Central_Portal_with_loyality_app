<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ExpireLoyaltyRewards;       
use App\Console\Commands\NotifyExpiringRewards;   

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

     protected $commands = [
        ExpireLoyaltyRewards::class,    
        NotifyExpiringRewards::class,   
    ];

    protected function schedule(Schedule $schedule)
    {
        $hour = config('app.hour');
        $min = config('app.min');
        $scheduledInterval = $hour !== '' ? ( ($min !== '' && $min != 0) ?  $min .' */'. $hour .' * * *' : '0 */'. $hour .' * * *') : '*/'. $min .' * * * *';
        if (env('IS_DEMO')){
            $schedule->command('migrate:fresh --seed')->cron($scheduledInterval);
        }
        $schedule->call(function () {
            \Laravel\Sanctum\PersonalAccessToken::where('expires_at', '<', now())->delete();
        })->everyMinute();

        // Expire rewards at midnight
        $schedule->command('loyalty:expire')->dailyAt('00:00');

        // Notify 3 days before expiry every morning
        $schedule->command('loyalty:notify-expiry')->dailyAt('09:00');
    }



    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
