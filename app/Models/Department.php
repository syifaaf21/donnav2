<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }
}
