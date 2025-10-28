<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FindingCategory extends Model
{
    use HasFactory;

    protected $table = 'tm_finding_categories';

    protected $fillable =
    [
        'name',
    ];

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}

