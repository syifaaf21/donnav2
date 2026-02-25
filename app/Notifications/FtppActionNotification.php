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
        // Determine URL by action if not explicitly provided
        if ($url) {
            $this->url = $url;
        } else {
            $act = strtolower($this->action);
            switch ($act) {
                case 'created':
                    // view auditee action for this finding
                    $this->url = url('/ftpp/auditee-action/' . ($this->finding->id ?? ''));
                    break;

                case 'assigned':
                case 'dept_head_checked':
                case 'auditor_approved':
                    // these actions are managed from the approval list
                    $this->url = route('approval.index');
                    break;

                case 'lead_approved':
                case 'auditee_revised':
                    // general ftpp listing
                    $this->url = url('/ftpp');
                    break;

                case 'auditor_return':
                    // open auditee action edit
                    $this->url = url('/ftpp/auditee-action/' . ($this->finding->id ?? '') . '/edit');
                    break;

                default:
                    $this->url = route('ftpp.index');
                    break;
            }
        }
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

                case 'auditor_return':
                    $message = "Auditor has returned the auditee action for finding (Registration No: {$reg}) for further revision.";
                    break;

                case 'auditee_revised':
                    $message = "Auditee has revised the action for finding (Registration No: {$reg}).";
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
