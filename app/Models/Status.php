<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'tm_statuses';

    protected $fillable = ['name'];

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }
}
