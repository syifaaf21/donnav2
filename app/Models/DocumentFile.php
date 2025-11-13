<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_mapping_id',
        'file_path',
        'original_name',
        'is_active',
        'archived_at',
        'replaced_by_id'
    ];

    protected $table = 'tt_document_files';

    public function mapping()
    {
        return $this->belongsTo(DocumentMapping::class);
    }

    public function auditFinding()
    {
        return $this->belongsTo(AuditFinding::class);
    }

    public function auditeeAction()
    {
        return $this->belongsTo(AuditeeAction::class);
    }
}
