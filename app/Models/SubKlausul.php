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
        'klausul_id',
        'name',
    ];

    public function headKlausul()
    {
        return $this->belongsTo(HeadKlausul::class);
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
