<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'document_number',
        'part_number_id',
        'department_id',
        'reminder_date',
        'deadline',
        'obsolete_date',
        'status_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
    'reminder_date' => 'date',
    'deadline' => 'date',
    ];

    protected $table = 'tt_document_mappings';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function partNumber()
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
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

    public function children()
    {
        return $this->hasMany(DocumentMapping::class, 'part_number_id', 'part_number_id')
            ->whereHas('document', function ($q) {
                $q->whereColumn('documents.parent_id', 'document_mappings.document_id');
            });
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }
}
