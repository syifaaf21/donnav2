<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentFile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use existing column as soft-deletes marker in this project.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $fillable = [
        'document_mapping_id',
        'audit_finding_id',
        'auditee_action_id',
        'file_path',
        'original_name',
        'is_active',
        'archived_at',
        'replaced_by_id',
        'marked_for_deletion_at',
        'pending_approval',
    ];

    protected $table = 'tt_document_files';

    public function mapping()
    {
        return $this->belongsTo(DocumentMapping::class, 'document_mapping_id');
    }

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class, 'audit_finding_id');
    }

    public function auditeeAction()
    {
        return $this->belongsTo(AuditeeAction::class, 'auditee_action_id');
    }

    public function replacedByFile()
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }
}
