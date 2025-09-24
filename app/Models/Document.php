<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
    'name',
    'parent_id',
    'department_id',
];


    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }
}
