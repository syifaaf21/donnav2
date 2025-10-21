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

    protected $table = 'models'; // sesuaikan

    public function partNumber()
    {
        return $this->hasMany(PartNumber::class, 'model_id', 'id');
    }
}
