<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentActionNotification extends Notification
{
    use Queueable;

    protected $action;           // revised | approved | rejected
    protected $byUser;           // nama user yang melakukan aksi
    protected $documentNumber;   // untuk Document Review
    protected $documentName;     // untuk Document Control
    protected $url;              // link ke halaman
    protected $departmentName;   // nama department pengupload

    public function __construct(
        $action,
        $byUser,
        $documentNumber = null,
        $documentName = null,
        $url = null,
        $departmentName = null
    ) {
        $this->action = $action;
        $this->byUser = $byUser;
        $this->documentNumber = $documentNumber;
        $this->documentName = $documentName;
        $this->url = $url;
        $this->departmentName = $departmentName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Format nama user, department, dan document
        $byUserFormatted = ucwords(strtolower($this->byUser));
        $departmentFormatted = $this->departmentName ? ucwords(strtolower($this->departmentName)) : null;

        $title = $this->documentNumber ?? ($this->documentName ?($this->documentName) : 'A Document');


        $message = '';

        switch ($this->action) {
            case 'revised':
                $message = $this->documentNumber
                    ? "{$title} has been revised by {$byUserFormatted}."
                    : "File for {$title} has been uploaded by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department {$departmentFormatted}" : "")
                    . " and is pending review.";
                break;

            case 'approved':
                $message = $this->documentNumber
                    ? "{$title} has been approved by {$byUserFormatted}."
                    : "Document {$title} has been approved by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . " and document is active.";
                break;

            case 'rejected':
                $message = $this->documentNumber
                    ? "{$title} has been rejected by {$byUserFormatted}."
                    : "Document {$title} has been rejected by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . ".";
                break;

            default:
                $message = "{$title} updated by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . ".";
                break;
        }

        return [
            'message' => $message,
            'url' => $this->url,
            'action' => $this->action, // keep action so view can style rejected red
        ];
    }
}
