<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubKlausul extends Model
{
    use HasFactory;

    protected $table = 'tm_sub_klausuls';

    protected $fillable =
    [
        'head_klausul_id',
        'code',
        'name',
    ];

    public function headKlausul()
    {
        return $this->belongsTo(HeadKlausul::class, 'head_klausul_id', 'id');
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
