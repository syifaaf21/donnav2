<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMappingDetail extends Model
{
    use HasFactory;

    protected $table = 'tt_document_mapping_part_number_model_product_process';

    protected $fillable = [
        'document_mapping_id',
        'part_number_id',
        'model_id',
        'product_id',
        'process_id',
    ];

    // Define relationships to PartNumber, ProductModel, Product, and Process
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

    // If needed, relationship to DocumentMapping for reverse access
    public function documentMapping()
    {
        return $this->belongsTo(DocumentMapping::class, 'document_mapping_id');
    }
}

