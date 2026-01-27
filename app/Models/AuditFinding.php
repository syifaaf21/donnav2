<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  AuditFinding extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use project-specific marked_for_deletion_at column for soft deletes.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $table = 'tt_audit_findings';

    protected $fillable =
        [
            'audit_type_id',
            'sub_audit_type_id',
            'finding_category_id',
            'department_id',
            'process_id',
            'product_id',
            'auditor_id',
            'registration_number',
            'finding_description',
            'status_id',
            'due_date',
        ];

    public function auditeeAction()
    {
        return $this->hasOne(AuditeeAction::class);
    }

    protected static function booted()
    {
        // Cascade soft-deletes to related models
        static::deleting(function ($finding) {
            // If force deleting, let DB handle cascades (if any)
            if ($finding->isForceDeleting()) {
                return;
            }

            // Soft-delete associated auditee action (if any)
            if ($finding->auditeeAction) {
                $finding->auditeeAction->delete();
            }

            // Soft-delete related document files
            \App\Models\DocumentFile::where('audit_finding_id', $finding->id)->delete();

            // Soft-delete pivot records for auditee and sub klausul
            \App\Models\AuditFindingAuditee::where('audit_finding_id', $finding->id)->delete();
            \App\Models\AuditFindingSubKlausul::where('audit_finding_id', $finding->id)->delete();
        });

        // Restore related models when parent restored
        static::restoring(function ($finding) {
            // Restore pivot records and files
            \App\Models\AuditFindingAuditee::withTrashed()->where('audit_finding_id', $finding->id)->restore();
            \App\Models\AuditFindingSubKlausul::withTrashed()->where('audit_finding_id', $finding->id)->restore();
            \App\Models\DocumentFile::withTrashed()->where('audit_finding_id', $finding->id)->restore();

            // Restore auditee action and its children
            $auditeeAction = \App\Models\AuditeeAction::withTrashed()->where('audit_finding_id', $finding->id)->first();
            if ($auditeeAction) {
                $auditeeAction->restore();
            }
        });
    }

    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_type_id');
    }

    public function subAudit()
    {
        return $this->belongsTo(SubAudit::class, 'sub_audit_type_id');
    }

    public function findingCategory()
    {
        return $this->belongsTo(FindingCategory::class);
    }

    public function subKlausuls()
    {
        return $this->belongsToMany(SubKlausul::class, 'tt_audit_finding_sub_klausul', 'audit_finding_id', 'sub_klausul_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : 'Unknown';
    }
     public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : '-';
    }

    public function getProcessNameAttribute()
    {
        return $this->process ? $this->process->name : '-';
    }

    public function getFindingCategoryNameAttribute()
    {
        return $this->findingCategory ? $this->findingCategory->name : '-';
    }

    public function getAuditTypeNameAttribute()
    {
        return $this->audit ? $this->audit->name : '-';
    }

    public function getAuditorNameAttribute()
    {
        return $this->auditor ? $this->auditor->name : '-';
    }

    public function getAuditeeNamesAttribute()
    {
        if ($this->auditee && $this->auditee->isNotEmpty()) {
            return $this->auditee->pluck('name')->join(', ');
        }
        return '-';
    }
    
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id')->whereHas('roles', function ($q) {
            $q->where('name', 'auditor');
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

    public function auditee()
    {
        return $this->belongsToMany(User::class, 'tt_audit_finding_auditee', 'audit_finding_id', 'auditee_id');
    }
}
