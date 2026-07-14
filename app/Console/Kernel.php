<?php

namespace App\Console;

use App\Jobs\LeaveEndReminderJob;
use App\Jobs\LeaveStartReminderJob;
use App\Jobs\LowBalanceReminderJob;
use App\Jobs\PendingApprovalReminderJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new PendingApprovalReminderJob())->weekdays()->dailyAt('09:30')->timezone('Asia/Kolkata');
        $schedule->job(new LeaveStartReminderJob())->dailyAt('08:00')->timezone('Asia/Kolkata');
        $schedule->job(new LeaveEndReminderJob())->dailyAt('18:00')->timezone('Asia/Kolkata');
        $schedule->job(new LowBalanceReminderJob())->monthlyOn(1, '09:00')->timezone('Asia/Kolkata');
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

