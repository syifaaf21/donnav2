<?php

namespace App\Console\Commands;

use App\Models\DocumentMapping;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDocumentControlReminder extends Command
{
    protected $signature = 'document:send-reminder';
    protected $description = 'Send WhatsApp reminders for documents based on reminder and obsolete dates.';

    public function handle(WhatsAppService $wa)
    {
        $now = Carbon::now()->format('Y-m-d');
        $groupId = config('services.whatsapp.group_id');

        $this->info("Running WhatsApp reminder for documents. Date: $now");

        // Dokumen yang belum obsolete dan reminder_date <= sekarang → trigger notifikasi
        $reminderDocs = DocumentMapping::with(['document', 'department'])
            ->whereDate('obsolete_date', '>', $now)
            ->whereNotNull('reminder_date')
            ->whereDate('reminder_date', '<=', $now)
            ->get()
            ->groupBy('department.name');

        if ($reminderDocs->isEmpty()) {
            $this->info("No documents with active reminders today. Skipping notification.");
            return;
        }

        // Dokumen yang sudah overdue / obsolete (tanggal obsolete < sekarang)
        $overdueDocs = DocumentMapping::with(['document', 'department'])
            ->whereDate('obsolete_date', '<', $now)
            ->get()
            ->groupBy('department.name');

        // Format pesan
        $message = "--- *DOCUMENT OBSOLESCENCE REMINDER* ---\n\n";
        $index = 1;

        foreach ($reminderDocs as $department => $docs) {
            $message .= "[$index] *{$department}*\n";
            foreach ($docs as $doc) {
                $daysLeft = Carbon::parse($doc->obsolete_date)->diffInDays($now);
                $message .= "- {$doc->document->name} : {$daysLeft} days left ❗\n";
            }
            $message .= "\n";
            $index++;
        }

        if ($overdueDocs->isNotEmpty()) {
            $message .= "--- *OVERDUE DOCUMENTS* ---\n\n";
            $index = 1;
            foreach ($overdueDocs as $department => $docs) {
                $message .= "[$index] *{$department}*\n";
                foreach ($docs as $doc) {
                    $daysOver = Carbon::parse($doc->obsolete_date)->diffInDays($now);
                    $message .= "- {$doc->document->name} : Overdue by {$daysOver} days ⚠️\n";
                }
                $message .= "\n";
                $index++;
            }
        }

        $message .= "*Please submit and verify to MS Department*\n";
        $message .= "------ BY AISIN BISA ------";

        // Kirim ke WhatsApp
        $sent = $wa->sendMessage($groupId, $message);

        if ($sent) {
            $this->info("WhatsApp message sent successfully to Group ID: $groupId");
        } else {
            $this->error("Failed to send WhatsApp message.");
        }
    }
}
