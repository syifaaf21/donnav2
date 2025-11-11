<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentActionNotification extends Notification
{
    use Queueable;

    protected $action;          // revised | approved | rejected
    protected $byUser;          // nama user yang melakukan aksi
    protected $documentNumber;  // untuk Document Review
    protected $documentName;    // untuk Document Control
    protected $url;             // link ke halaman

    public function __construct($action, $byUser, $documentNumber = null, $documentName = null, $url = null)
    {
        $this->action = $action;
        $this->byUser = $byUser;
        $this->documentNumber = $documentNumber;
        $this->documentName = $documentName;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $title = $this->documentNumber ?? $this->documentName ?? 'A document';
        $message = '';

        switch ($this->action) {
            case 'revised':
                $message = $this->documentNumber
                    ? "{$title} has been revised by {$this->byUser}."
                    : "File for {$title} has been uploaded by {$this->byUser} and is pending review.";
                break;

            case 'approved':
                $message = $this->documentNumber
                    ? "{$title} has been approved by {$this->byUser}."
                    : "File for {$title} has been approved by {$this->byUser} and document is active.";
                break;

            case 'rejected':
                $message = $this->documentNumber
                    ? "{$title} has been rejected by {$this->byUser}."
                    : "File for {$title} has been rejected by {$this->byUser}.";
                break;

            default:
                $message = "{$title} updated by {$this->byUser}.";
                break;
        }

        return [
            'message' => $message,
            'url' => $this->url,
        ];
    }
}
