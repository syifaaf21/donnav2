<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditeeAction extends Model
{
    use HasFactory;

    protected $table = 'tt_auditee_actions';

    protected $fillable =
    [
        'audit_finding_id',
        'status_id',
        'user_id',
        'root_cause',
        'yokoten',
        'yokoten_area',
        'verified_by_auditor',
        'auditee_signature',
        'dept_head_signature',
        'auditor_signature',
    ];

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
