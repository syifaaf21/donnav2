<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentActionNotification extends Notification
{
    use Queueable;

    protected $action;           // revised | approved | rejected
    protected $byUser;           // nama user yang melakukan aksi
    protected $documentNumber;   // untuk Document Review
    protected $documentName;     // untuk Document Control
    protected $url;              // link ke halaman
    protected $departmentName;   // nama department pengupload
    protected $details;          // detail metadata document for email

    public function __construct(
        $action,
        $byUser,
        $documentNumber = null,
        $documentName = null,
        $url = null,
        $departmentName = null,
        $details = []
    ) {
        $this->action = $action;
        $this->byUser = $byUser;
        $this->documentNumber = $documentNumber;
        $this->documentName = $documentName;
        $this->url = $url;
        $this->departmentName = $departmentName;
        $this->details = is_array($details) ? $details : [];
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        $hasEmail = !empty($notifiable->email);
        $isApprovalStageAction = in_array($this->action, ['checked_by_supervisor', 'approved_by_dept_head'], true);
        $isSupervisorRevisionAction = false;

        if ($this->action === 'revised' && $hasEmail && method_exists($notifiable, 'roles')) {
            $isSupervisorRevisionAction = $notifiable->roles()
                ->whereRaw('LOWER(name) = ?', ['supervisor'])
                ->exists();
        }

        // Keep in-app as-is, and add email for approval stages + supervisor revision queue.
        if ($hasEmail && ($isApprovalStageAction || $isSupervisorRevisionAction)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $byUserFormatted = ucwords(strtolower($this->byUser));
        $title = $this->documentNumber ?? ($this->documentName ?: 'Document');
        $url = $this->url ?: url('/');
        $departmentFormatted = $this->departmentName ? ucwords(strtolower($this->departmentName)) : null;

        $model = trim((string) ($this->details['model'] ?? ''));
        $product = trim((string) ($this->details['product'] ?? ''));
        $process = trim((string) ($this->details['process'] ?? ''));
        $partNumber = trim((string) ($this->details['part_number'] ?? ''));
        $revisionNotes = trim((string) ($this->details['revision_notes'] ?? ''));

        if ($this->action === 'revised') {
            $mail = (new MailMessage)
                ->subject("Document Needs Supervisor Check - {$title}")
                ->greeting('Hello,')
                ->line("Document {$title} has been revised by {$byUserFormatted} and needs your check as Supervisor.")
                ->line("Document Number: {$title}");

            if (!empty($model)) {
                $mail->line("Model: {$model}");
            }
            if (!empty($product)) {
                $mail->line("Product: {$product}");
            }
            if (!empty($process)) {
                $mail->line("Process: {$process}");
            }
            if (!empty($partNumber)) {
                $mail->line("Part Number: {$partNumber}");
            }
            if (!empty($departmentFormatted)) {
                $mail->line("Department: {$departmentFormatted}");
            }
            if (!empty($revisionNotes)) {
                $mail->line("Revision Notes: {$revisionNotes}");
            }

            return $mail
                ->line('Action Required (Supervisor): Login to your account, open menu Document Review, then open Approval Queue and check this document.')
                ->action('Open Approval Queue', $url)
                ->line('Please review the document as soon as possible.');
        }

        if ($this->action === 'checked_by_supervisor') {
            $mail = (new MailMessage)
                ->subject("Document Needs Dept Head Approval - {$title}")
                ->greeting('Hello,')
                ->line("Document {$title} has been checked by {$byUserFormatted} and needs Dept Head approval.")
                ->line("Document Number: {$title}");

            if (!empty($model)) {
                $mail->line("Model: {$model}");
            }
            if (!empty($product)) {
                $mail->line("Product: {$product}");
            }
            if (!empty($process)) {
                $mail->line("Process: {$process}");
            }
            if (!empty($partNumber)) {
                $mail->line("Part Number: {$partNumber}");
            }
            if (!empty($departmentFormatted)) {
                $mail->line("Department: {$departmentFormatted}");
            }
            if (!empty($revisionNotes)) {
                $mail->line("Revision Notes: {$revisionNotes}");
            }

            return $mail
                ->line('Action Required (Dept Head): Login to your account, open menu Document Review, then open Approval Queue and approve/reject this document.')
                ->action('Open Approval Queue', $url)
                ->line('Please review the document as soon as possible.');
        }

        if ($this->action === 'approved_by_dept_head') {
            $mail = (new MailMessage)
                ->subject("Document Needs Admin Review - {$title}")
                ->greeting('Hello,')
                ->line("Document {$title} has been approved by {$byUserFormatted} and now needs Admin review.")
                ->line("Document Number: {$title}");

            if (!empty($model)) {
                $mail->line("Model: {$model}");
            }
            if (!empty($product)) {
                $mail->line("Product: {$product}");
            }
            if (!empty($process)) {
                $mail->line("Process: {$process}");
            }
            if (!empty($partNumber)) {
                $mail->line("Part Number: {$partNumber}");
            }
            if (!empty($departmentFormatted)) {
                $mail->line("Department: {$departmentFormatted}");
            }
            if (!empty($revisionNotes)) {
                $mail->line("Revision Notes: {$revisionNotes}");
            }

            return $mail
                ->line('Action Required: Login to your account, open menu Document Review, then open Approval Queue and continue the review process.')
                ->action('Open Approval Queue', $url)
                ->line('Please review the document as soon as possible.');
        }

        return (new MailMessage)
            ->subject('Document Notification')
            ->line('You have a new document notification.')
            ->action('Open', $url);
    }

    public function toDatabase($notifiable)
    {
        // Format nama user, department, dan document
        $byUserFormatted = ucwords(strtolower($this->byUser));
        $departmentFormatted = $this->departmentName ? ucwords(strtolower($this->departmentName)) : null;

        $title = $this->documentNumber ?? ($this->documentName ?($this->documentName) : 'A Document');


        $message = '';

        switch ($this->action) {
            case 'checked_by_supervisor':
                $message = $this->documentNumber
                    ? "{$title} has been checked by {$byUserFormatted} and need your approval"
                    : "Document {$title} has been checked by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . " and need your approval";
                break;

            case 'approved_by_dept_head':
                $message = $this->documentNumber
                    ? "{$title} has been approved by {$byUserFormatted} and need your approval"
                    : "Document {$title} has been approved by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . " and need your approval";
                break;

            case 'revised':
                $message = $this->documentNumber
                    ? "{$title} has been revised by {$byUserFormatted} and need your review"
                    : "File for {$title} has been uploaded by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department {$departmentFormatted}" : "")
                    . " and is pending review.";
                break;

            case 'approved':
                $message = $this->documentNumber
                    ? "{$title} has been reviewed and approved by {$byUserFormatted}. Document is now active."
                    : "Document {$title} has been approved by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . " and document is active.";
                break;

            case 'rejected':
                $message = $this->documentNumber
                    ? "{$title} has been rejected by {$byUserFormatted}. Please revise and upload again."
                    : "Document {$title} has been rejected by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . ".";
                break;

            default:
                $message = "{$title} updated by {$byUserFormatted}"
                    . ($departmentFormatted ? " on department ({$departmentFormatted})" : "")
                    . ".";
                break;
        }

        return [
            'message' => $message,
            'url' => $this->url,
            'action' => $this->action, // keep action so view can style rejected red
        ];
    }
}
