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
        'parent_id',
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
