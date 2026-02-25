<?php

namespace App\Observers;

use App\Models\AuditFinding;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Notifications\DeptHeadNeedCheckNotification;
use App\Notifications\FtppActionNotification;
use Illuminate\Support\Facades\Notification;

class AuditFindingObserver
{
    public function updated(AuditFinding $finding)
    {
        // only act when status_id changed
        if (! $finding->isDirty('status_id')) {
            return;
        }

        $old = $finding->getOriginal('status_id');
        $new = $finding->status_id;

        // resolve names safely
        $oldName = optional($finding->status()->getModel()->find($old))->name;
        $newName = optional($finding->status()->getModel()->find($new))->name;

        if (! $oldName || ! $newName) return;

        if (strtolower($oldName) === 'need assign' && strtolower($newName) === 'need check') {
            // find dept heads for the finding's department
            $deptId = $finding->department_id;
            if (empty($deptId)) return;

            $deptHeads = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['dept head']))
                ->where(function ($q) use ($deptId) {
                    $q->whereExists(function ($sub) use ($deptId) {
                        $sub->select(\DB::raw(1))
                            ->from('tt_user_department')
                            ->whereColumn('tt_user_department.user_id', 'users.id')
                            ->where('tt_user_department.department_id', $deptId);
                    });

                    if (Schema::hasColumn('users', 'department_id')) {
                        $q->orWhere('department_id', $deptId);
                    }
                })
                ->get();

            if ($deptHeads->isEmpty()) {
                \Log::info('AuditFindingObserver: no dept heads found for department_id ' . $deptId);
                return;
            }

            // determine reply-to from auditee action 'pic' user if available
            $auditeeAction = $finding->auditeeAction;
            $replyTo = null;
            if ($auditeeAction && $auditeeAction->pic) {
                try {
                    $user = User::find($auditeeAction->pic);
                    $replyTo = $user->email ?? null;
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // prepare recipients
            $allDeptHeads = $deptHeads->unique('id')->values();
            $mailRecipients = $allDeptHeads->filter(fn($u) => !empty($u->email))->values();

            $reg = $finding->registration_number ?? 'N/A';
            $customMessage = "Finding (No: {$reg}) needs your review (status changed to Need Check).";

            // db notification for all
            Notification::send($allDeptHeads, new FtppActionNotification($finding, 'assigned', null, $customMessage));

            // email for those with email
            if ($mailRecipients->isNotEmpty()) {
                Notification::send($mailRecipients, new DeptHeadNeedCheckNotification($finding, $auditeeAction, null, $replyTo));
            } else {
                \Log::warning('AuditFindingObserver: no dept head emails for department_id ' . $deptId);
            }
        }
    }
}
