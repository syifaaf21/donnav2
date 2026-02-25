<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\AuditFinding;
use App\Models\User;
use App\Notifications\FindingDueNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class SendFindingDueNotifications extends Command
{
    protected $signature = 'notify:findings-due';
    protected $description = 'Send notifications 3 days before due date, on due date, and 1 day after due date for findings';

    public function handle(WhatsAppService $wa)
    {
        $today = Carbon::now()->startOfDay();
        $todayDate = $today->toDateString();

        $findings = AuditFinding::whereNotNull('due_date')->get();

        $totalSent = 0;

        // Prepare buckets for WhatsApp message
        $buckets = [
            'in_3' => [],
            'tomorrow' => [],
            'today' => [],
            'overdue_1' => [],
        ];

        foreach ($findings as $finding) {
            try {
                $due = Carbon::parse($finding->due_date)->startOfDay();
                $days = $today->diffInDays($due, false); // signed difference

                // Only process findings that are 'need assign'
                $statusName = strtolower(optional($finding->status)->name ?? '');
                if ($statusName !== 'need assign') {
                    continue;
                }

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

                // Add to WhatsApp buckets (one line per finding)
                $displayDept = optional($finding->department)->name ? ' | Dept: ' . optional($finding->department)->name : '';
                $displayAuditor = $finding->auditor?->name ? ' | Auditor: ' . $finding->auditor->name : '';
                $line = "- {$finding->registration_number} (due {$due->toDateString()}){$displayDept}{$displayAuditor}";

                if ($days === 3) {
                    $buckets['in_3'][] = $line;
                } elseif ($days === 1) {
                    $buckets['tomorrow'][] = $line;
                } elseif ($days === 0) {
                    $buckets['today'][] = $line;
                } elseif ($days === -1) {
                    $buckets['overdue_1'][] = $line;
                }
            } catch (\Throwable $e) {
                Log::error('Error sending finding due notification for id ' . $finding->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Finding due notifications sent: {$totalSent}");
        // Build WhatsApp message and send if there are entries
        $groupId = config('services.whatsapp.group_id');

        $waMessage = "📌 *FINDING DUE NOTIFICATIONS*\n";
        $waMessage .= "_(Automated Whatsapp Notification)_\n\n";

        $hasAny = false;
        $appendBucket = function ($label, $items) use (&$waMessage, &$hasAny) {
            if (empty($items)) return;
            $hasAny = true;
            $waMessage .= "*{$label}*\n";
            $counter = 1;
            foreach ($items as $line) {
                $waMessage .= "    {$counter}. {$line}\n";
                $counter++;
            }
            $waMessage .= "\n";
        };

        $appendBucket("Due in 3 days", $buckets['in_3']);
        $appendBucket("Due Tomorrow", $buckets['tomorrow']);
        $appendBucket("Due Today", $buckets['today']);
        $appendBucket("Overdue by 1 day", $buckets['overdue_1']);

        if ($hasAny && $groupId) {
            $waMessage .= "-- Automated Notice --";
            try {
                $sent = $wa->sendGroupMessage($groupId, $waMessage);
                if ($sent) {
                    $this->info("WhatsApp group message sent to: {$groupId}");
                } else {
                    $this->error("Failed to send WhatsApp message.");
                }
            } catch (\Throwable $e) {
                Log::error('Error sending WhatsApp finding notifications: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
