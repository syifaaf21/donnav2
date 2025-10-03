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

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

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
