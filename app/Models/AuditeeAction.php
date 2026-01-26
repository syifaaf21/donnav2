<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditeeAction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use project-specific marked_for_deletion_at column for soft deletes.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $table = 'tt_auditee_actions';

    protected $fillable =
    [
        'audit_finding_id',
        'pic',
        'root_cause',
        'yokoten',
        'yokoten_area',
        'verified_by_auditor',
        'acknowledge_by_lead_auditor',
        'effectiveness_verification',
        'ldr_spv_signature',
        'dept_head_signature',
        'ldr_spv_id',
        'dept_head_id',
        'auditor_id',
        'lead_auditor_id',
    ];

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function whyCauses()
    {
        return $this->hasMany(WhyCauses::class);
    }

    public function correctiveActions()
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    public function preventiveActions()
    {
        return $this->hasMany(PreventiveAction::class);
    }

    public function file()
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ldr_spv_id');
    }

    public function deptHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dept_head_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    protected static function booted()
    {
        static::deleting(function ($action) {
            if ($action->isForceDeleting()) {
                return;
            }

            // Soft-delete related why/causes, corrective, preventive and files
            \App\Models\WhyCauses::where('auditee_action_id', $action->id)->delete();
            \App\Models\CorrectiveAction::where('auditee_action_id', $action->id)->delete();
            \App\Models\PreventiveAction::where('auditee_action_id', $action->id)->delete();
            \App\Models\DocumentFile::where('auditee_action_id', $action->id)->delete();
        });

        static::restoring(function ($action) {
            \App\Models\WhyCauses::withTrashed()->where('auditee_action_id', $action->id)->restore();
            \App\Models\CorrectiveAction::withTrashed()->where('auditee_action_id', $action->id)->restore();
            \App\Models\PreventiveAction::withTrashed()->where('auditee_action_id', $action->id)->restore();
            \App\Models\DocumentFile::withTrashed()->where('auditee_action_id', $action->id)->restore();
        });
    }
}
