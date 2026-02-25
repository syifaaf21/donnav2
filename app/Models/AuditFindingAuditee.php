<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditFindingAuditee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use project-specific marked_for_deletion_at column for soft deletes.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $table = 'tt_audit_finding_auditee';

    protected $fillable = ['audit_finding_id', 'auditee_id'];

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'auditee_id');
    }
}
