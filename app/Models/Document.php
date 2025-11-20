<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'tm_documents';

    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'code',
        'department_id',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public static function getTypes()
    {
        return [
            'control' => 'Control',
            'review' => 'Review',
        ];
    }

    public function allMappingsCount()
    {
        $count = $this->mapping()->count();
        foreach ($this->childrenRecursive as $child) {
            $count += $child->allMappingsCount();
        }
        return $count;
    }

    public function allMappingsForPlant($plant)
    {
        // pastikan gunakan relationship mapping() yang sudah didefinisikan
        return $this->mapping->filter(function ($mapping) use ($plant) {
            if ($mapping->partNumber) {
                return strtolower($mapping->partNumber->plant ?? '') === strtolower($plant);
            }
            if ($mapping->productModel) {
                return strtolower($mapping->productModel->plant ?? '') === strtolower($plant);
            }
            return false;
        });
    }
}
