<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentCreatedNotification extends Notification
{
    use Queueable;

    protected $documentNumber;
    protected $documentName;
    protected $createdBy;
    protected $url;
    protected $documentId;

    public function __construct($createdBy, $documentNumber = null, $documentName = null, $url = null, $documentId = null)
    {
        $this->createdBy = $createdBy;
        $this->documentNumber = $documentNumber;
        $this->documentName = $documentName;
        $this->url = $url;
        $this->documentId = $documentId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $title = $this->documentNumber ?? $this->documentName ?? 'A new document';

        return [
            'message' => "{$title} has been created by {$this->createdBy}. Please upload the file",
            'url' => $this->url,
            'document_id' => $this->documentId,
        ];
    }
}

