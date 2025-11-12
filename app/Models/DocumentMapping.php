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
        'model_id',
        'product_id',
        'process_id',
        'part_number_id',
        'parent_id',
        'department_id',
        'reminder_date',
        'deadline',
        'obsolete_date',
        'status_id',
        'notes',
        'user_id',
        'last_reminder_date',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'deadline' => 'date',
    ];

    protected $table = 'tt_document_mappings';

    public function latestFile()
    {
        // Mengambil satu file terbaru dari relasi files()
        return $this->hasOne(DocumentFile::class)->latestOfMany();
    }

   public function getFilesForModalAttribute()
{
    // Hanya file aktif untuk ditampilkan di UI (view tombol/dropdown)
    $allFiles = $this->files()
        ->where('is_active', true)
        ->orderBy('created_at', 'asc')
        ->get();

    return $allFiles->map(function ($file) {
        return [
            'id' => $file->id,
            'name' => $file->original_name,
            'document_name' => $this->document->name,
            'url' => Storage::url($file->file_path),
            'is_active' => (int) $file->is_active,
            'created_at' => $file->created_at->toDateTimeString(),
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
            'name' => $file->original_name,
            'document_name' => $this->document->name,
            'url' => Storage::url($file->file_path),
            'is_active' => (int) $file->is_active,
            'replaced_by_id' => $file->replaced_by_id,
            'created_at' => $file->created_at->toDateTimeString(),
        ];
    })->values()->toArray();
}

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function partNumber()
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
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
        return $this->hasMany(DocumentFile::class);
    }
}