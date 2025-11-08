<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    protected $table = 'tm_processes';

    protected $fillable = ['name', 'code', 'plant'];

    public function partNumbers()
    {
        return $this->hasMany(PartNumber::class);
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
