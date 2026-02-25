<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NeedApprovalByAuditorNotification extends Notification
{
    use Queueable;

    protected $finding;
    protected $ftppApproval;
    protected $byUserName;
    protected $replyToEmail;
    protected $url;
    protected $dueDate;

    public function __construct($finding, $ftppApproval = null, $byUserName = null, $replyToEmail = null, $url = null, $dueDate = null)
    {
        $this->finding = $finding;
        $this->ftppApproval = $ftppApproval;
        $this->byUserName = $byUserName;
        $this->replyToEmail = $replyToEmail;
        $this->url = $url ?? route('ftpp.index');
        $this->dueDate = $dueDate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $reg = $this->finding->registration_number ?? 'N/A';

        $mail = (new MailMessage)
            ->subject("[FTPP] Need Approval – Finding {$reg}")
            ->greeting('Hello ' . ($notifiable->name ?? 'Auditor') . ',')
            ->line("Finding No: {$reg} requires your approval.")
            ->line('Please review the FTPP and respond accordingly.');

        if (!empty($this->dueDate)) {
            $due = $this->dueDate instanceof \DateTimeInterface
                ? $this->dueDate->format('d M Y')
                : (string) $this->dueDate;
            $mail->line("Due date: {$due}");
        }

        $mail->action('Open FTPP', $this->url)
            ->line('Please use a laptop and the AIIA network when completing this task.')
            ->line('Thank you for your attention.');

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
            'message' => "Finding (No: {$reg}) needs your approval.",
            'finding_id' => $this->finding->id ?? null,
            'url' => $this->url,
            'due_date' => $this->dueDate ? ($this->dueDate instanceof \DateTimeInterface ? $this->dueDate->format('Y-m-d') : $this->dueDate) : null,
        ];
    }
}
