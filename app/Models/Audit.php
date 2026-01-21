<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $table = 'tm_audit_types';

    protected $fillable = [
        'name',
        'prefix_code',
        'registration_number_format',
    ];

    public function subAudit()
    {
        return $this->hasMany(SubAudit::class, 'audit_type_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'audit_type_id');
    }
}
