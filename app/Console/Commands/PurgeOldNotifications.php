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
        $oneMonthAgo = Carbon::now()->subMonth();

        $deleted = DB::table('notifications')
            ->where('created_at', '<', $oneMonthAgo)
            ->delete();
        $this->info("Deleted $deleted old notifications.");
    }
}
