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
        $schedule->command('documents:check-obsolete')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground();
        // Jalankan SendDocumentReviewReminder setiap Kamis jam 08:00
        $schedule->command('document:send-review-reminder')
            ->weeklyOn(4, '08:00') // 4 = Kamis
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
        // Hapus dokumen dan mapping yang sudah expired (hard delete)
        $schedule->command('documents:delete-expired')
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
