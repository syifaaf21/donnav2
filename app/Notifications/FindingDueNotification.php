<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FindingDueNotification extends Notification
{
    use Queueable;

    protected $finding;
    protected $message;
    protected $url;

    public function __construct($finding, $message, $url = null)
    {
        $this->finding = $finding;
        $this->message = $message;
        $this->url = $url ?? route('ftpp.index');

    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'finding_id' => $this->finding->id,
            'url' => $this->url ,
        ];
    }


}
