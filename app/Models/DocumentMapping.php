<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'part_number_id',
        'status_id',
        'document_number',
        'type',
        'version',
        'file_path',
        'notes',
        'obsolete_date',
        'reminder_date',
        'deadline',
        'user_id',
    ];


    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function partNumber()
    {
        return $this->belongsTo(PartNumber::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this -> belongsTo (Department::class);
    }
}
