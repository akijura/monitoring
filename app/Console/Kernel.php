<?php

namespace App\Console;

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
        Commands\checkStatus::class,
        Commands\Permissions::class,
        Commands\canMessages::class,
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
        $schedule->command('check:status')
                 ->everyMinute();
        $schedule->command('give:permissions')
                ->between('9:00', '9:10'); //Send message has been False (Kun yopish gurpasida)
        $schedule->command('can:messages')
                ->between('17:55', '18:05'); //Send message has been True (Kun yopish gurpasida)
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
