<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Jalankan SendDocumentControlReminder setiap hari jam 08:00
        $schedule->command('document:send-reminder')
            ->weeklyOn(1, '08:00') // 1 = Senin
            ->withoutOverlapping()
            ->runInBackground();
        // Opsional: jalankan di background
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
