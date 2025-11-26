<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;  // Import model User yang kamu buat

class Role extends Model
{
    use HasFactory;

    protected $table = 'tm_roles';

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tt_user_role', 'role_id', 'user_id');
    }
}
