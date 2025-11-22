<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'plant',
    ];

    protected $table = 'tm_products'; // sesuaikan kalau nama tabel beda

    public function partNumber()
    {
        return $this->hasMany(PartNumber::class, 'product_id', 'id');
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
