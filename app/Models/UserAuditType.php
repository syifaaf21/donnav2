<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAuditType extends Model
{
    use HasFactory;

    protected $table = 'tt_user_audit_type';

    protected $fillable = [
        'user_id',
        'user_role_id',
        'audit_id',
        'is_auditor',
        'is_lead_auditor',
    ];

    protected $casts = [
        'is_auditor' => 'boolean',
        'is_lead_auditor' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }
}
