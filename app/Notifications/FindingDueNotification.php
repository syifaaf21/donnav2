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

    public function __construct($finding, $message)
    {
        $this->finding = $finding;
        $this->message = $message;
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
            'url' => route('ftpp.index'),
        ];
    }


}
