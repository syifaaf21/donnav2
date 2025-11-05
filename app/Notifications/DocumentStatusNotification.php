<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentStatusNotification extends Notification
{
    use Queueable;

    protected $documentName;
    protected $status;
    protected $updatedBy;

    /**
     * @param string $documentName
     * @param string $status
     * @param string $updatedBy
     */
    public function __construct($documentName, $status, $updatedBy)
    {
        $this->documentName = $documentName;
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
            'message' => "Document '{$this->documentName}' has been marked as {$this->status} by {$this->updatedBy}.",
            'url' => route('document-review.index'),
        ];
    }
}
