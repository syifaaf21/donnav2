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
        'audit_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}
