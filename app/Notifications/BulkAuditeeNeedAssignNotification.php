<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkAuditeeNeedAssignNotification extends Notification
{
    use Queueable;

    /** @var array */
    private array $registrationNumbers;

    /** @var \Illuminate\Support\Collection|null */
    private $findings;

    public function __construct(array $registrationNumbers, $findings = null)
    {
        $this->registrationNumbers = $registrationNumbers;
        $this->findings = $findings;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $displayName = $notifiable->name ?? ($notifiable->email ?? 'Colleague');

        $mail = (new MailMessage)
            ->subject('Action required: FTPP assignments require your attention')
            ->greeting("Hello {$displayName},")
            ->line('You have been assigned as an auditee for the following FTPP(s). Please review each item and complete the required actions:');

        // Add each registration number as a neat bullet list
        foreach ($this->registrationNumbers as $reg) {
            $mail->line("• {$reg}");
        }

        $mail->line('Required actions:')
            ->line('- Complete the Why-Cause (5 Whys) analysis')
            ->line('- Provide the root cause')
            ->line('- Fill in Corrective and Preventive Actions')
            ->action('Open your FTPP dashboard', url('/ftpp'))
            ->salutation('Regards,')
            ->line('Quality Management');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'registrations' => $this->registrationNumbers,
            'count' => count($this->registrationNumbers),
        ];
    }
}
