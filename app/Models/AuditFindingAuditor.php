<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFindingAuditor extends Model
{
    use HasFactory;

    protected $table = 'tt_audit_finding_auditors';

    protected $fillable = [
        'audit_finding_id',
        'auditor_id',
    ];

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
