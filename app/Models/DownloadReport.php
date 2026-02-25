<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadReport extends Model
{
    use HasFactory;

    protected $table = 'tt_download_report';

    protected $fillable = [
        'document_mapping_id',
        'user_id',
        'document_file_id',
    ];

    public function documentMapping(): BelongsTo
    {
        return $this->belongsTo(DocumentMapping::class, 'document_mapping_id');
    }

    public function documentFile(): BelongsTo
    {
        return $this->belongsTo(DocumentFile::class, 'document_file_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
