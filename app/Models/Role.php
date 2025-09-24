<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Role extends Model
{
    use HasFactory;

    public function user ()
    {
<<<<<<< HEAD
        return $this->hasMany(User::class);
=======
        return $this->belongsTo(User::class);
>>>>>>> master
    }
}
