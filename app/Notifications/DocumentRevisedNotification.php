<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRevisedNotification extends Notification
{
    use Queueable;

    protected $documentNumber;
    protected $revisedBy;

    public function __construct($documentNumber, $revisedBy)
    {
        $this->documentNumber = $documentNumber;
        $this->revisedBy = $revisedBy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Document {$this->documentNumber} diupdate {$this->revisedBy}.",
            'url' => route('document-review.index'),
        ];
    }
}
