<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    const WORK_RANGE = ["8:00", "23:55"]; // 工作时间 - +8时区

    const SLEEP_RANGE = ["0:01", "7:59"]; // 静默时间 - +8时区

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Test\Test',
        'App\Console\Commands\Task\TaskCommand',

    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('Task')->dailyAt('09:30')->runInBackground(); // 订正数据
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
