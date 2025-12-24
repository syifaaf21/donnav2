<?php

namespace App\Console\Commands;

use App\Models\DocumentMapping;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDocumentReviewReminder extends Command
{
    protected $signature = 'document:send-review-reminder';
    protected $description = 'Send WhatsApp reminders for Document Review approaching deadline or overdue.';

    public function handle(WhatsAppService $wa)
    {
        $groupId = config('services.whatsapp.group_id');

        $this->info("Running WhatsApp notification for Document Review.");

        // Ambil dokumen review dengan status Approved dan memiliki notes revisi
        $docs = DocumentMapping::with(['document', 'department', 'status', 'partNumber', 'productModel'])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->get();

        if ($docs->isEmpty()) {
            $this->info("No Document Review with notes to send notification.");
            return;
        }

        // Format pesan
        $message = "📌 *DOCUMENT REMINDER ALERT*\n";
        $message .= "_(Automated Whatsapp Notification)_\n\n";

        $docCounter = 1;

        // Format setiap dokumen
        foreach ($docs as $doc) {
            $message .= "{$docCounter}. *Department:* " . ($doc->department->name ?? 'N/A') . "\n";
            $message .= "   *Document Number:* {$doc->document_number}\n";
            $message .= "   *Model:* " . ($doc->productModel->name ?? 'N/A') . "\n";
            $message .= "   *Notes:* {$doc->notes}\n\n";
            $docCounter++;
        }

        // Footer
        $message .= "------ *BY AISIN BISA* ------";

        // Kirim ke WhatsApp
        $sent = $wa->sendGroupMessage($groupId, $message);

        if ($sent) {
            $this->info("WhatsApp message sent successfully to Group ID: $groupId");
        } else {
            $this->error("Failed to send WhatsApp message.");
        }
    }
}
