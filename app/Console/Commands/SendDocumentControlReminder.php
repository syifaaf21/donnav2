<?php

namespace App\Console\Commands;

use App\Models\DocumentMapping;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDocumentControlReminder extends Command
{
    protected $signature = 'document:send-reminder';
    protected $description = 'Send WhatsApp reminders efficiently for all documents.';

    public function handle(WhatsAppService $wa)
    {
        $today = Carbon::now();
        if (!$today->isMonday()) {
            $this->info("Not Monday, skipping WhatsApp reminder.");
            return;
        }

        $now = Carbon::now()->startOfDay(); // gunakan Carbon object
        $groupId = config('services.whatsapp.group_id');

        $this->info("Running WhatsApp reminder for documents. Date: {$now->toDateString()}");

        // Ambil semua dokumen sekaligus
        $docs = DocumentMapping::with(['document', 'department', 'status'])->get();

        if ($docs->isEmpty()) {
            $this->info("No documents to send reminders today.");
            return;
        }

        // Inisialisasi array kategori
        $categories = [
            'uncomplete' => [],
            'active' => [],
            'obsolete_today' => [],
            'overdue' => [],
        ];

        // Kategorikan dokumen
        foreach ($docs as $doc) {
            $status = $doc->status->name ?? '';
            $obsoleteDate = Carbon::parse($doc->obsolete_date);
            $reminderDate = $doc->reminder_date ? Carbon::parse($doc->reminder_date) : null;

            // Uncomplete
            if ($status === 'Uncomplete') {
                if (!$doc->last_reminder_date || Carbon::parse($doc->last_reminder_date)->lt($now)) {
                    $categories['uncomplete'][$doc->department->name][] = $doc;
                    $doc->last_reminder_date = $now;
                    $doc->save();
                }
            }
            // Active dengan reminder jatuh tempo
            elseif ($status === 'Active' && $obsoleteDate->gt($now) && $reminderDate && $reminderDate->lte($now)) {
                $categories['active'][$doc->department->name][] = $doc;
            }
            // Obsolete hari ini — HANYA jika statusnya bukan Uncomplete
            elseif ($status !== 'Uncomplete' && $obsoleteDate->eq($now)) {
                $categories['obsolete_today'][$doc->department->name][] = $doc;
            }
            // Overdue — HANYA jika statusnya bukan Uncomplete
            elseif ($status !== 'Uncomplete' && $obsoleteDate->lt($now)) {
                $categories['overdue'][$doc->department->name][] = $doc;
            }
        }

        // Cek apakah ada dokumen di kategori apapun
        $hasDocs = false;
        foreach ($categories as $cat => $docsByDept) {
            if (!empty($docsByDept)) {
                $hasDocs = true;
                break;
            }
        }

        if (!$hasDocs) {
            $this->info("No documents require reminders today.");
            return; // hentikan command tanpa kirim pesan
        }

        // Format pesan
        $message = "📌 *DOCUMENT CONTROL REMINDER*\n";
        $message .= "_(TESTING)_\n\n";


        $formatCategory = function ($label, $docsByDept) use (&$message) {
            if (empty($docsByDept)) return;
            $deptCounter = 1;
            $message .= "$label\n";
            foreach ($docsByDept as $dept => $docs) {
                $message .= "{$deptCounter}. *{$dept}*\n";
                foreach ($docs as $doc) {
                    $message .= $doc->_message;
                }
                $message .= "\n";
                $deptCounter++;
            }
        };

        // Siapkan _message per kategori
        foreach ($categories['uncomplete'] as $dept => $docs) {
            foreach ($docs as $doc) {
                $doc->_message = "    - *{$doc->document->name}* → ⚠️ Please upload the file\n";
            }
        }
        foreach ($categories['active'] as $dept => $docs) {
            foreach ($docs as $doc) {
                $daysLeft = Carbon::parse($doc->obsolete_date)->diffInDays($now);
                $doc->_message = "    - *{$doc->document->name}* → ⚠️ {$daysLeft} day(s) left until obsolete\n";
            }
        }
        foreach ($categories['obsolete_today'] as $dept => $docs) {
            foreach ($docs as $doc) {
                $doc->_message = "    - *{$doc->document->name}* → Obsolete today\n";
            }
        }
        foreach ($categories['overdue'] as $dept => $docs) {
            foreach ($docs as $doc) {
                $daysOver = Carbon::parse($doc->obsolete_date)->diffInDays($now);
                $doc->_message = "    - *{$doc->document->name}* → Overdue by {$daysOver} day(s)  ❗\n";
            }
        }

        // Tambahkan kategori ke pesan
        $formatCategory("⏳ *UNCOMPLETE DOCUMENTS (File not Uploaded)*", $categories['uncomplete']);
        $formatCategory("🔔 *DOCUMENTS OBSOLENCE REMINDER*", $categories['active']);
        $formatCategory("⚠️ *DOCUMENTS OBSOLETE TODAY*", $categories['obsolete_today']);
        $formatCategory("⏰ *OVERDUE DOCUMENTS*", $categories['overdue']);

        // Footer
        $message .= "*Action Required:* Please submit and verify to MS Department.\n";
        $message .= "------ *BY AISIN BISA* ------";

        // Kirim ke WhatsApp
        $sent = $wa->sendMessage($groupId, $message);

        if ($sent) {
            $this->info("WhatsApp message sent successfully to Group ID: $groupId". config('services.whatsapp.group_id'));
        } else {
            $this->error("Failed to send WhatsApp message.");
        }
    }
}
