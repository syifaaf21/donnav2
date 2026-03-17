<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurgeOldNotifications extends Command
{
    protected $signature = 'notifications:purge-old';
    protected $description = 'Delete notifications older than 1 month';

    public function handle()
    {

        // Change to 2 months
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        $deleted = DB::table('notifications')
            ->where('created_at', '<', $twoMonthsAgo)
            ->delete();
        $this->info("Deleted $deleted notifications older than 2 months.");
    }
}
