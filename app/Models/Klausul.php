<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Klausul extends Model
{
    use HasFactory;

    protected $table = 'tm_klausuls';

    protected $fillable =
    [
        'name',
        'audit_type_id',
    ];

    public function headKlausul()
    {
        return $this->hasMany(HeadKlausul::class, 'klausul_id', 'id');
    }

    public function auditType()
    {
        return $this->belongsTo(Audit::class, 'audit_type_id', 'id');
    }
}
