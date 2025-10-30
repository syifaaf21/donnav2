<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFinding extends Model
{
    use HasFactory;

    protected $table = 'tt_audit_findings';

    protected $fillable =
        [
            'audit_type_id',
            'sub_audit_type_id',
            'finding_category_id',
            'sub_klausul_id',
            'department_id',
            'process_id',
            'auditor_id',
            'auditee_id',
            'registration_number',
            'finding_description',
            'status_id',
            'due_date',
        ];

    public function auditeeAction()
    {
        return $this->hasMany(AuditeeAction::class);
    }

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function subAudit()
    {
        return $this->belongsTo(SubAudit::class);
    }

    public function findingCategory()
    {
        return $this->belongsTo(FindingCategory::class);
    }

    public function subKlausul()
    {
        return $this->belongsTo(SubKlausul::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id')->whereHas('role', function ($q) {
            $q->where('name', 'auditor');
        });
    }

    public function auditee()
    {
        return $this->belongsTo(User::class, 'auditee_id')->whereHas('role', function ($q) {
            $q->where('name', 'auditee');
        });
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function file()
    {
        return $this->hasMany(DocumentFile::class);
    }
}
