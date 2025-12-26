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

    /**
     * Convert HTML from Quill editor to WhatsApp markdown format
     */
    private function convertHtmlToWhatsApp($html)
    {
        if (empty($html)) {
            return '';
        }

        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Paragraph
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/p>/i', "\n", $text);
        $text = preg_replace('/<p[^>]*>/i', '', $text);

        // Italic
        $text = preg_replace_callback('/<(em|i)>(.*?)<\/\1>/is', function ($m) {
            return '_' . trim($m[2]) . '_';
        }, $text);

        // Bold (PASTIKAN ADA SPASI DI DEPAN JIKA SEBELUMNYA MARKER)
        $text = preg_replace_callback('/<(strong|b)>(.*?)<\/\1>/is', function ($m) {
            $content = '*' . trim($m[2]) . '*';

            // Tambahkan spasi jika sebelumnya bukan spasi / newline
            return ' ' . $content;
        }, $text);

        // Strikethrough
        $text = preg_replace_callback('/<(s|del|strike)>(.*?)<\/\1>/is', function ($m) {
            return ' ~' . trim($m[2]) . '~';
        }, $text);

        // Underline (WA tidak support)
        $text = preg_replace('/<u>(.*?)<\/u>/is', '$1', $text);

        // Remove remaining tags
        $text = strip_tags($text);

        // Cleanup spasi berlebih (aman)
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);

        return trim($text);
    }

    public function handle(WhatsAppService $wa)
    {
        $groupId = config('services.whatsapp.group_id');
        $this->info("Running WhatsApp notification for Document Review (pending approvals).");

        // Ambil dokumen review Approved dengan notes, belum pernah dikirim untuk approval terbaru
        $docs = DocumentMapping::with(['document', 'department', 'status', 'partNumber', 'productModel'])
            ->whereHas('document', fn($q) => $q->where('type', 'review'))
            ->whereHas('status', fn($q) => $q->where('name', 'Approved'))
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->whereNotNull('last_approved_at')
            ->where(function ($q) {
                $q->whereNull('review_notified_at')
                    ->orWhereColumn('review_notified_at', '<', 'last_approved_at');
            })
            ->get();

        if ($docs->isEmpty()) {
            $this->info("No Document Review with notes to send notification.");
            return;
        }

        // Format pesan
        $message = "📌 *DOCUMENT REVIEW ALERT*\n";
        $message .= "_(Automated Whatsapp Notification)_\n\n";
        $message .= "The following Documents have been revised and approved: \n\n";

        $groupedDocs = $docs->groupBy(fn($doc) => $doc->department ? $doc->department->name : 'N/A');

        $deptCounter = 1;

        foreach ($groupedDocs as $deptName => $deptDocs) {
            $message .= "[{$deptCounter}] *{$deptName}*\n"; // Department di-bold

            $docCounter = 1;
            foreach ($deptDocs as $doc) {
                $modelNames = $doc->productModel->isNotEmpty()
                    ? $doc->productModel->pluck('name')->join(', ')
                    : 'N/A';

                $notes = $this->convertHtmlToWhatsApp($doc->notes);

                $message .= "{$docCounter}. *Document Number* : {$doc->document_number}\n"; // Judul bold
                $message .= "   *Model* : {$modelNames}\n"; // Judul bold
                $message .= "   *Revision Notes* : {$notes}\n\n"; // Judul bold

                $docCounter++;
            }

            $deptCounter++;
        }

        // Footer
        $message .= "*Action Required:* Please review the revised documents at your earliest convenience and upload to *MADONNA* on *Document Review Menu*.\n\n";
        $message .= "------ *BY AISIN BISA* ------";

        // Kirim ke WhatsApp
        $sent = $wa->sendGroupMessage($groupId, $message);

        if ($sent) {
            // Tandai sudah dikirim untuk approval tersebut
            $now = Carbon::now();
            foreach ($docs as $doc) {
                $doc->timestamps = false;
                $doc->updateQuietly(['review_notified_at' => $now]);
                $doc->timestamps = true;
            }
            $this->info("WhatsApp message sent successfully to Group ID: $groupId");
        } else {
            $this->error("Failed to send WhatsApp message.");
        }
    }
}
