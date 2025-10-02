<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'type'
    ];

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

     public function children()
    {
        return $this->hasMany(Document::class, 'parent_id'); // recursive
    }

    // Optional: untuk menampilkan children hanya dengan part number yang sama
    // public function childrenSamePart()
    // {
    //     return $this->hasMany(Document::class, 'parent_id')
    //                 ->where('part_number_id', $this->part_number_id)
    //                 ->with('childrenSamePart');
    // }

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }

    public static function getTypes()
    {
        return [
            'control' => 'Control',
            'review' => 'Review',
        ];
    }

}
