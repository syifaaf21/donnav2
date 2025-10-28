<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $table = 'tm_audits';

    protected $fillable = [
        'name',
    ];

    public function subAudit()
    {
        return $this->hasMany(SubAudit::class);
    }
}
