<?php

namespace App\Console;

use App\Console\Commands\DeleteLaravelLog;
use App\Jobs\DistributeVendorCommissionJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new DistributeVendorCommissionJob)->cron('* * * * *');
        $schedule->command('payments:reconcile-keepz-split')
            ->everyFiveMinutes()
            ->withoutOverlapping();
        $schedule->command('log:clean')->daily();
        $schedule->command('tokens:cleanup')->everyFiveMinutes();
    }

    protected $commands = [
        DeleteLaravelLog::class,
    ];
}
