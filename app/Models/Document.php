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
        // Count only active mappings (not marked for deletion)
        $count = $this->mapping()->whereNull('marked_for_deletion_at')->count();
        foreach ($this->childrenRecursive as $child) {
            $count += $child->allMappingsCount();
        }
        return $count;
    }
    public function allMappingsForPlant($plant)
    {
        $plantLower = strtolower($plant);

        // Query only active mappings and include related partNumber/productModel to evaluate plant
        return $this->mapping()
            ->whereNull('marked_for_deletion_at')
            ->with(['partNumber', 'productModel'])
            ->get()
            ->filter(function ($mapping) use ($plantLower) {
                $hasPartPlant = $mapping->partNumber->contains(fn($pn) => strtolower($pn->plant) === $plantLower);
                $hasModelPlant = $mapping->productModel->contains(fn($model) => strtolower($model->plant) === $plantLower);

                return $hasPartPlant || $hasModelPlant;
            });
    }
}
