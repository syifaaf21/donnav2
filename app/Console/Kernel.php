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

        // Jalankan SendDocumentReviewReminder setiap Jumat jam 08:00
            // Jalankan SendDocumentReviewReminder setiap hari kerja jam 08:00 (command akan skip weekend)
            $schedule->command('document:send-review-reminder')
                ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Opsional: jalankan di background
        $schedule->command('archive:delete-expired')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command('notify:findings-due')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command('notifications:purge-old')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
