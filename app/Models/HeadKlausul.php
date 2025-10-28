<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeadKlausul extends Model
{
    use HasFactory;

    protected $table = 'tm_head_klausuls';

    protected $fillable =
    [
        'klausul_id',
        'name',
    ];

    public function klausul()
    {
        return $this->belongsTo(Klausul::class);
    }

    public function subKlausul()
    {
        return $this->hasMany(SubKlausul::class);
    }
}
