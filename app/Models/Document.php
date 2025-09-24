<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'parent_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }
}