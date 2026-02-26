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
        'docspace_file_id',
        'docspace_folder_id',
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

    /**
     * Scope: only active files (not deleted)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('marked_for_deletion_at');
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->original_name ?? basename($this->file_path);
    }

    public function getFileTypeAttribute(): string
    {
        return match ($this->extension) {
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'powerpoint',
            'pdf' => 'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            default => 'other',
        };
    }
}
