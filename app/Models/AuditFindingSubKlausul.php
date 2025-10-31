<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFindingSubKlausul extends Model
{
    use HasFactory;

    protected $table = 'tt_audit_finding_sub_klausul';

    protected $fillable =
    [
        'audit_finding_id',
        'sub_klausul_id',
    ];

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }

    public function subKlausul()
    {
        return $this->belongsTo(SubKlausul::class, 'sub_klausul_id');
    }
}
