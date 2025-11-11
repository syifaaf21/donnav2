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
    protected $url; // 🔹 Tambahkan properti URL

    /**
     * @param string $documentName
     * @param string $status
     * @param string $updatedBy
     * @param string|null $url
     */
    public function __construct($documentName, $status, $updatedBy, $url = null)
    {
        $this->documentName = $documentName;
        $this->status = $status;
        $this->updatedBy = $updatedBy;
        $this->url = $url; // 🔹 Simpan URL
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Document '{$this->documentName}' has been marked as {$this->status}.",
            'url' => $this->url ?? route('document-control.index'), // 🔹 Gunakan $this->url, fallback ke document control
        ];
    }
}
