<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    use HasFactory;

    protected $fillable = ['document_mapping_id', 'file_path'];

     public function mapping()
    {
        return $this->belongsTo(DocumentMapping::class);
    }
}
