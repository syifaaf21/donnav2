<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AuditFinding;

class AuditeeNeedAssignNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private AuditFinding $finding;

    public function __construct(AuditFinding $finding)
    {
        $this->finding = $finding;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $finding = $this->finding;
        $reg = $finding->registration_number ?? ('#' . $finding->id);
        $url = url('/ftpp/'. 'auditee-action/' . $finding->id );

        return (new MailMessage)
            ->subject("Action required: Complete Why-Cause & Actions for {$reg}")
            ->greeting('Hello,')
            ->line("You have been assigned as an auditee for finding {$reg}.")
            ->line('Please complete the Why-Cause analysis (5 Whys), provide the root cause, and fill in Corrective & Preventive Actions as soon as possible.')
            ->action('Open Finding', $url)
            ->line('Thank you for your prompt action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'finding_id' => $this->finding->id,
            'registration_number' => $this->finding->registration_number,
        ];
    }
}
