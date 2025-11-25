<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'npk',
        'email',
        'password',
        'audit_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    // Many-to-many relation: users <-> roles (pivot: tt_user_role)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tt_user_role', 'user_id', 'role_id');
    }

    // Many-to-many relation: users <-> departments (pivot: tt_user_department)
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'tt_user_department', 'user_id', 'department_id');
    }

    public function auditFindingsAsAuditee()
    {
        return $this->belongsToMany(AuditFinding::class, 'tt_audit_finding_auditee', 'auditee_id', 'audit_finding_id');
    }

    public function auditFindingsAsAuditor()
    {
        return $this->hasMany(AuditFinding::class, 'auditor_id');
    }

    public function auditType()
    {
        return $this->belongsTo(Audit::class, 'audit_type_id');
    }
}
