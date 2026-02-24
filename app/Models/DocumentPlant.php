<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPlant extends Model
{
    use HasFactory;

    protected $table = 'document_plant';

    protected $fillable = [
        'document_id',
        'plant',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
