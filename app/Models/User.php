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

    // Many-to-many relation: users <-> audit types (pivot: tt_user_audit_type)
    public function auditTypes()
    {
        return $this->belongsToMany(Audit::class, 'tt_user_audit_type', 'user_id', 'audit_id');
    }

    /**
     * Check if user is supervisor
     */
    public function isSupervisor()
    {
        return $this->roles()->where('name', 'Supervisor')->exists();
    }

    /**
     * Check if user is supervisor of specific department
     */
    public function isSupervisorOfDepartment($departmentId)
    {
        return $this->isSupervisor() &&
               $this->departments()->where('tm_departments.id', $departmentId)->exists();
    }

    /**
     * Check if user can edit document mapping (supervisor dari dept pemilik dokumen)
     *
     * @param \App\Models\DocumentMapping $mapping
     * @return bool
     */
    public function canEditDocument($mapping)
    {
        return $this->isSupervisorOfDepartment($mapping->department_id);
    }
}
