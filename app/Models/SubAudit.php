<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAudit extends Model
{
    use HasFactory;

    protected $table = 'tm_sub_audit_types';

    protected $fillable =
    [
        'audit_type_id',
        'name',
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
