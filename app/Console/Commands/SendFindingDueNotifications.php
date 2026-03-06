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
    protected $description = 'Send finding due notifications on Tuesday/Wednesday/Friday with weekend-safe overdue calculation';

    public function handle(WhatsAppService $wa)
    {
        $today = Carbon::now()->startOfDay();
        $todayDate = $today->toDateString();

        // Send only on Tuesday, Wednesday, and Friday.
        if (!in_array($today->dayOfWeek, [Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::FRIDAY], true)) {
            $this->info("Today is {$today->format('l')}. Skipping; notifications run only on Tuesday, Wednesday, and Friday.");
            return 0;
        }

        $findings = AuditFinding::whereNotNull('due_date')->get();

        $totalSent = 0;

        // Prepare buckets for WhatsApp message
        $buckets = [
            'in_3' => [],
            'tomorrow' => [],
            'today' => [],
            'overdue' => [],
        ];

        foreach ($findings as $finding) {
            try {
                $due = Carbon::parse($finding->due_date)->startOfDay();
                $days = $today->diffInDays($due, false); // signed difference

                $statusName = optional($finding->status)->name ?? '-';
                $normalizedStatus = strtolower(trim($statusName));
                if (in_array($normalizedStatus, ['close', 'draft', 'draft finding'], true)) {
                    continue;
                }

                $overdueBusinessDays = $this->countBusinessOverdueDays($due, $today);

                $message = match ($days) {
                    3 => "Finding {$finding->registration_number} is due in 3 days ({$due->toDateString()}).",
                    1 => "Finding {$finding->registration_number} is due tomorrow ({$due->toDateString()}).",
                    0 => "Finding {$finding->registration_number} is due today ({$due->toDateString()}).",
                    default => null
                };

                if ($message === null && $overdueBusinessDays > 0) {
                    $message = "Finding {$finding->registration_number} is overdue by {$overdueBusinessDays} business day(s) (due {$due->toDateString()}).";
                }

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

                if ($days === 3) {
                    $buckets['in_3'][] = $finding;
                } elseif ($days === 1) {
                    $buckets['tomorrow'][] = $finding;
                } elseif ($days === 0) {
                    $buckets['today'][] = $finding;
                } elseif ($overdueBusinessDays > 0) {
                    $buckets['overdue'][] = [
                        'finding' => $finding,
                        'days' => $overdueBusinessDays,
                    ];
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
            foreach ($items as $item) {
                $finding = is_array($item) ? ($item['finding'] ?? null) : $item;
                if (!$finding) {
                    continue;
                }
                $auditeeNames = $finding->auditee()->pluck('name')->filter()->values();
                $auditeeText = $auditeeNames->isNotEmpty() ? $auditeeNames->implode(', ') : '-';
                $departmentName = optional($finding->department)->name ?? '-';
                $auditorName = $finding->auditor?->name ?? '-';
                $statusName = optional($finding->status)->name ?? '-';
                $dueDate = Carbon::parse($finding->due_date)->toDateString();
                $overdueText = is_array($item) && isset($item['days'])
                    ? "Overdue: {$item['days']} business day(s)\n"
                    : '';

                $waMessage .= "[{$counter}] {$finding->registration_number}\n";
                $waMessage .= "Department: {$departmentName}\n";
                $waMessage .= "Auditee: {$auditeeText}\n";
                $waMessage .= "Auditor: {$auditorName}\n";
                $waMessage .= "Status: {$statusName}\n\n";
                $waMessage .= "Due Date: {$dueDate}\n";
                $waMessage .= $overdueText;
                $waMessage .= "\n";
                $counter++;
            }
        };

        $appendBucket("Due in 3 days", $buckets['in_3']);
        $appendBucket("Due Tomorrow", $buckets['tomorrow']);
        $appendBucket("Due Today", $buckets['today']);
        $appendBucket("Overdue", $buckets['overdue']);

        if ($hasAny && $groupId) {
            // Footer
            $waMessage .= "*Action Required:* Please check your FTPP items in *MADONNA* under the *FTPP Menu*.\n";
            $waMessage .= "------ *BY AISIN BISA* ------";
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

    /**
     * Count overdue days in business days (Saturday/Sunday are not counted).
     */
    private function countBusinessOverdueDays(Carbon $due, Carbon $today): int
    {
        if ($today->lessThanOrEqualTo($due)) {
            return 0;
        }

        $cursor = $due->copy()->addDay();
        $count = 0;

        while ($cursor->lessThanOrEqualTo($today)) {
            if (!$cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }
}
