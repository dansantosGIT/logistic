<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendReturnReminders::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // daily at 08:00 server time
        $schedule->command('reminders:send-expected-returns')->dailyAt('08:00');
    }
}
