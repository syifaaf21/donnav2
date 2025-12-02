<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\AuditFinding;
use App\Models\User;
use App\Notifications\FindingDueNotification;
use Carbon\Carbon;

class SendFindingDueNotifications extends Command
{
    protected $signature = 'notify:findings-due';
    protected $description = 'Send notifications 3 days before due date, on due date, and 1 day after due date for findings';

    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        $todayDate = $today->toDateString();

        $findings = AuditFinding::whereNotNull('due_date')->get();

        $totalSent = 0;

        foreach ($findings as $finding) {
            try {
                $due = Carbon::parse($finding->due_date)->startOfDay();
                $days = $today->diffInDays($due, false); // signed difference

                $message = match ($days) {
                    3 => "Finding {$finding->registration_number} is due in 3 days ({$due->toDateString()}).",
                    1 => "Finding {$finding->registration_number} is due tomorrow ({$due->toDateString()}).",
                    0 => "Finding {$finding->registration_number} is due today ({$due->toDateString()}).",
                    -1 => "Finding {$finding->registration_number} is overdue by 1 day (due {$due->toDateString()}).",
                    default => null
                };

                if (!$message) {
                    continue;
                }

                // AMBIL RECIPIENTS
                $recipients = collect();

                if ($finding->auditor) {
                    $recipients->push($finding->auditor);
                }

                // auditee() relation may be many-to-many
                $recipients = $recipients->merge($finding->auditee()->get());

                // Admin users
                $admins = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['admin']))->get();
                $recipients = $recipients->merge($admins);

                $recipients = $recipients->unique('id')->filter()->values();

                // Kirim per user, cegah duplikat pada hari yang sama
                foreach ($recipients as $user) {
                    $alreadySent = $user->notifications()
                        ->where('type', FindingDueNotification::class)
                        ->where('data->finding_id', $finding->id)
                        ->whereDate('created_at', $todayDate)
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    $user->notify(new FindingDueNotification($finding, $message));
                    $totalSent++;
                }
            } catch (\Throwable $e) {
                Log::error('Error sending finding due notification for id ' . $finding->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Finding due notifications sent: {$totalSent}");
        return 0;
    }
}
