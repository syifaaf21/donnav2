<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetPasswordChangedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:set-password-changed-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set password_changed_at = now() for existing users where null, excluding Admin and Super Admin roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating users...');

        User::whereNull('password_changed_at')->chunk(100, function($users) {
            foreach ($users as $user) {
                if (!$user->roles()->whereIn('name', ['Admin','Super Admin'])->exists()) {
                    $user->update(['password_changed_at' => now()]);
                    $this->info("Updated user id={$user->id}");
                } else {
                    $this->info("Skipped admin user id={$user->id}");
                }
            }
        });

        $this->info('Done.');
        return 0;
    }
}
