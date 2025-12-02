<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FtppActionNotification extends Notification
{
    use Queueable;

    protected $finding;
    protected $action;     // created | assigned | dept_head_checked | auditor_approved | lead_approved | custom
    protected $byUser;
    protected $customMessage;
    protected $url;

    public function __construct($finding, $action, $byUser = null, $customMessage = null, $url = null)
    {
        $this->finding = $finding;
        $this->action = $action;
        $this->byUser = $byUser ? ucwords(strtolower($byUser)) : null;
        $this->customMessage = $customMessage;
        $this->url = $url ?? route('ftpp.index');
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $reg = $this->finding->registration_number;
        $by = $this->byUser ?? 'User';

        // If message manually passed, use it
        if ($this->customMessage) {
            $message = $this->customMessage;
        } else {
            // Auto-generate message based on action type
            switch ($this->action) {

                case 'created':
                    $message = "Please immediately assign the finding (Registration No: {$reg}).";
                    break;

                case 'assigned':
                    $message = "{$by} has assigned the finding (Registration No: {$reg}).";
                    break;

                case 'dept_head_checked':
                    $message = "Dept Head has reviewed the auditee action for finding (Registration No: {$reg}).";
                    break;

                case 'auditor_approved':
                    $message = "Auditor has approved the auditee action for finding (Registration No: {$reg}).";
                    break;

                case 'lead_approved':
                    $message = "Lead Auditor has closed the finding (Registration No: {$reg}).";
                    break;

                default:
                    // generic fallback
                    $message = "Finding {$reg} has been updated by {$by}.";
                    break;
            }
        }

        return [
            'message' => $message,
            'finding_id' => $this->finding->id,
            'url' => $this->url,
        ];
    }
}
