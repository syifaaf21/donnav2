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

    /** @var \DateTimeInterface|string|null */
    private $dueDate;

    public function __construct(array $registrationNumbers, $findings = null, $dueDate = null)
    {
        $this->registrationNumbers = $registrationNumbers;
        $this->findings = $findings;
        $this->dueDate = $dueDate;
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

        // Show due date if provided
        if ($this->dueDate) {
            $due = $this->dueDate instanceof \DateTimeInterface
                ? $this->dueDate->format('d M Y')
                : (string) $this->dueDate;
            $mail->line("Due date: {$due}");
        }

        $mail->line('Required actions:')
            ->line('- Complete the Why-Cause (5 Whys) analysis')
            ->line('- Provide the root cause')
            ->line('- Fill in Corrective and Preventive Actions')
            ->action('Open your FTPP dashboard', url('/ftpp'))
            ->line('')
            ->line('Please use a laptop and the AIIA network when completing this task.')
            ->line('Thank you for your prompt attention to these assignments.')
            ->salutation("Regards,\n\nManagement System Team");
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
            'due_date' => $this->dueDate ? ($this->dueDate instanceof \DateTimeInterface ? $this->dueDate->format('Y-m-d') : $this->dueDate) : null,
        ];
    }
}
