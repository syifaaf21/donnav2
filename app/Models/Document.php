<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DocumentPlant;

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
        // return $this->children()->with('childrenRecursive');
                return $this->children()->whereNull('marked_for_deletion_at')->with('childrenRecursive');
    }

    public function mapping()
    {
        return $this->hasMany(DocumentMapping::class);
    }

    /**
     * Plants mapping for this document (pivot-like table `document_plant`).
     */
    public function plants()
    {
        return $this->hasMany(DocumentPlant::class, 'document_id');
    }

    /**
     * Check whether this document is assigned to a given plant (case-insensitive).
     * Accepts 'others' and maps it to 'all'.
     */
    public function hasPlant(string $plant): bool
    {
        $p = strtolower($plant);
        if ($p === 'others') $p = 'all';
        return $this->plants->contains(fn($row) => strtolower($row->plant) === $p);
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
