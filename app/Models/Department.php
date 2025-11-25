<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'tm_departments';

    protected $fillable = [
        'name',
        'code',
        'plant',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tt_user_department', 'department_id', 'user_id');
    }

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function auditFinding()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
