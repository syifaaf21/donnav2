<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentStatusNotification extends Notification
{
    use Queueable;

    protected $documentNumber;
    protected $status;
    protected $updatedBy;

    public function __construct($documentNumber, $status, $updatedBy)
    {
        $this->documentNumber = $documentNumber;
        $this->status = $status;
        $this->updatedBy = $updatedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Document {$this->documentNumber} has been {$this->status} by {$this->updatedBy}.",
            'url' => route('document-review.index'),
        ];
    }
}
