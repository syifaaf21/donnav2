<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRevisionApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $documentNumber,
        protected ?string $documentName,
        protected string $byUser,
        protected string $url,
        protected array $details = [],
        protected ?string $revisionNotes = null
    ) {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $title = $this->documentNumber ?: ($this->documentName ?: 'Document');
        $byUserFormatted = ucwords(strtolower($this->byUser));
        $revisionNotes = trim((string) $this->revisionNotes);
        $model = trim((string) ($this->details['model'] ?? ''));
        $product = trim((string) ($this->details['product'] ?? ''));
        $process = trim((string) ($this->details['process'] ?? ''));
        $partNumber = trim((string) ($this->details['part_number'] ?? ''));

        $mail = (new MailMessage)
            ->subject("Revision Approved - {$title}")
            ->greeting('Hello,')
            ->line("Document {$title} has been approved after revision by MS Department.")
            ->line("Document Number: {$title}");

        if ($model !== '') {
            $mail->line("Model: {$model}");
        }

        if ($product !== '') {
            $mail->line("Product: {$product}");
        }

        if ($process !== '') {
            $mail->line("Process: {$process}");
        }

        if ($partNumber !== '') {
            $mail->line("Part Number: {$partNumber}");
        }

        if ($revisionNotes !== '') {
            $mail->line("Revision Notes: {$revisionNotes}");
        }

        return $mail
            ->line('This approved revision may affect your document. Please review the document in MADONNA Document Review Menu.')
            ->action('Open Document Review', $this->url)
            ->line('Please review it at your earliest convenience.');
    }
}
