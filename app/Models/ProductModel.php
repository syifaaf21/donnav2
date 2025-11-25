<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plant',
    ];

    protected $table = 'tm_models'; // sesuaikan

    public function partNumber()
    {
        return $this->hasMany(PartNumber::class, 'model_id', 'id');
    }
    public function documentMappings()
    {
        return $this->belongsToMany(DocumentMapping::class, 'tt_document_mapping_models', 'model_id', 'document_mapping_id');
    }
}
