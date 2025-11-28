<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $table = 'tt_document_mapping_status_histories';

    protected $fillable = [
        'document_mapping_id',
        'status_id',
    ];

    public function mapping()
    {
        return $this->belongsTo(DocumentMapping::class, 'document_mapping_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
