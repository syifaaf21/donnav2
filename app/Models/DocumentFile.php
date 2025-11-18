<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_mapping_id',
        'audit_finding_id',
        'auditee_action_id',
        'file_path',
        'original_name',
        'is_active',
        'archived_at',
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
}
