<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use App\Models\AuditFinding;
use App\Observers\AuditFindingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Paginator::useTailwind(); // Tambahkan ini
        // register model observer for AuditFinding to send notifications on status change
        AuditFinding::observe(AuditFindingObserver::class);
    }
}
