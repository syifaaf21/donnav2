<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class DocumentUpdatedNotification extends Notification
{
    use Queueable;

    protected $documentNumber;
    protected $updatedBy;
    // protected $context; // misalnya 'Review' atau 'Control'

    public function __construct($documentNumber, $updatedBy)
    {
        $this->documentNumber = $documentNumber;
        $this->updatedBy = $updatedBy;
        // $this->context = $context;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->documentNumber} has been updated by {$this->updatedBy}.",
            'url' => route('document-review.index'), // bisa juga dikondisikan sesuai context
        ];
    }
}

