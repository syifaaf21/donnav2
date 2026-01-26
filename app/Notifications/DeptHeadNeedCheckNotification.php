<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DeptHeadNeedCheckNotification extends Notification
{
    use Queueable;

    protected $finding;
    protected $auditeeAction;
    protected $byUserName;
    protected $replyToEmail;
    protected $url;

    public function __construct($finding, $auditeeAction = null, $byUserName = null, $replyToEmail = null, $url = null)
    {
        $this->finding = $finding;
        $this->auditeeAction = $auditeeAction;
        $this->byUserName = $byUserName;
        $this->replyToEmail = $replyToEmail;
        $this->url = $url ?? route('ftpp.index');
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $reg = $this->finding->registration_number ?? 'N/A';

        $mail = (new MailMessage)
            ->subject("[FTPP] Need Check – Finding {$reg}")
            ->greeting('Hello ' . ($notifiable->name ?? 'Dept Head') . ',')
            ->line("Finding No: {$reg} requires your review.")
            ->line('Please review the auditee action and respond accordingly.')
            ->action('Open FTPP', $this->url);

        if (!empty($this->replyToEmail)) {
            $mail->replyTo($this->replyToEmail);
        }

        // Change regards to authenticated user name if available
        if (!empty($this->byUserName)) {
            $mail->salutation("Regards,\n\n{$this->byUserName}");
        } else {
            $mail->salutation('Regards');
        }

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        $reg = $this->finding->registration_number ?? 'N/A';
        return [
            'message' => "Finding (No: {$reg}) needs your review.",
            'finding_id' => $this->finding->id ?? null,
            'url' => $this->url,
        ];
    }
}
