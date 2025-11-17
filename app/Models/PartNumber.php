<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_number',
        'product_id',
        'model_id',
        'process',
    ];

    protected $table = 'part_numbers'; // sesuaikan

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class, 'model_id', 'id');
    }
}
