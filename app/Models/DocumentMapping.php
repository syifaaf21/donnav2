<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class DocumentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'document_number',
        'revision',
        'model_id',
        'product_id',
        'process_id',
        'part_number_id',
        'parent_id',
        'department_id',
        'reminder_date',
        'deadline',
        'obsolete_date',
        'period_years',
        'status_id',
        'notes',
        'initial_notes',
        'user_id',
        'last_reminder_date',
        'last_approved_at',
        'review_notified_at',
        'plant',
        'marked_for_deletion_at',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'deadline' => 'date',
        'last_approved_at' => 'datetime',
        'review_notified_at' => 'datetime',
    ];

    protected $table = 'tt_document_mappings';

    public function latestFile()
    {
        // Mengambil satu file terbaru dari relasi files()
        return $this->hasOne(DocumentFile::class)->latestOfMany();
    }

    public function getFilesForModalAttribute()
    {
        // Kembalikan file-file yang relevan untuk modal beserta metadata
        // Sertakan file aktif, file yang punya flag pending, dan info pengarsipan
        $files = $this->files()
            ->orderBy('created_at', 'asc')
            ->get();

        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                // Backwards compatibility: some views expect 'name'
                'name' => $file->original_name,
                'document_name' => $this->document->name,
                'file_path' => $file->file_path,
                'url' => Storage::url($file->file_path),
                'is_active' => (int) $file->is_active,
                'pending_approval' => $file->pending_approval,
                'replaced_by_id' => $file->replaced_by_id,
                'marked_for_deletion_at' => $file->marked_for_deletion_at ? $file->marked_for_deletion_at->toDateTimeString() : null,
                'file_type' => $file->file_type,
                'uploaded_by' => $file->uploaded_by,
                'created_at' => $file->created_at->toDateTimeString(),
                'size' => Storage::disk('public')->exists($file->file_path) ? Storage::disk('public')->size($file->file_path) : null,
            ];
        })->values()->toArray();
    }

    /** Jika butuh semua file (aktif + nonaktif) untuk audit/modal detail: */
    public function getFilesForModalAllAttribute()
    {
        $allFiles = $this->files()->orderBy('created_at', 'asc')->get();

        return $allFiles->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                // Backwards compatibility: some views expect 'name'
                'name' => $file->original_name,
                'document_name' => $this->document->name,
                'file_path' => $file->file_path,
                'url' => Storage::url($file->file_path),
                'is_active' => (int) $file->is_active,
                'pending_approval' => $file->pending_approval,
                'replaced_by_id' => $file->replaced_by_id,
                'marked_for_deletion_at' => $file->marked_for_deletion_at ? $file->marked_for_deletion_at->toDateTimeString() : null,
                'file_type' => $file->file_type,
                'uploaded_by' => $file->uploaded_by,
                'created_at' => $file->created_at->toDateTimeString(),
                'size' => Storage::disk('public')->exists($file->file_path) ? Storage::disk('public')->size($file->file_path) : null,

            ];
        })->values()->toArray();
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function partNumber()
    {
        return $this->belongsToMany(
            PartNumber::class,
            'tt_document_mapping_part_numbers', // nama tabel pivot
            'document_mapping_id',       // FK di pivot ke document_mapping
            'part_number_id'                   // FK di pivot ke model
        )->withTimestamps();
    }

    public function productModel()
    {
        return $this->belongsToMany(
            ProductModel::class,
            'tt_document_mapping_models', // nama tabel pivot
            'document_mapping_id',       // FK di pivot ke document_mapping
            'model_id'                   // FK di pivot ke model
        )->withTimestamps();
    }

    public function product()
    {
        return $this->belongsToMany(
            Product::class,
            'tt_document_mapping_products', // nama tabel pivot
            'document_mapping_id',       // FK di pivot ke document_mapping
            'product_id'                   // FK di pivot ke model
        )->withTimestamps();
    }

    public function process()
    {
        return $this->belongsToMany(
            Process::class,
            'tt_document_mapping_processes', // nama tabel pivot
            'document_mapping_id',       // FK di pivot ke document_mapping
            'process_id'                   // FK di pivot ke model
        )->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(DocumentMapping::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentMapping::class, 'parent_id');
    }

    public function files()
    {
        // Hanya file aktif yang masih terhubung ke mapping
        return $this->hasMany(DocumentFile::class);
    }

    /**
     * Ambil file archive (document_mapping_id null dan replaced_by_id tidak null)
     */
    // public static function archiveFiles()
    // {
    //     return \App\Models\DocumentFile::whereNull('document_mapping_id')
    //         ->whereNotNull('replaced_by_id');
    // }
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function latestStatusHistory()
    {
        return $this->hasOne(StatusHistory::class)->latestOfMany();
    }
}
