<?php
namespace App\Modules\Shop\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zofe\Rapyd\Traits\Sortable;
use Zofe\Rapyd\Traits\SSearch;


class ProductCategory extends Model
{
    use SoftDeletes, SSearch, Sortable;

    protected $fillable = ['name', 'slug', 'parent_id'];


    public function priceListItems()
    {
        return $this->hasManyThrough(PricelistItem::class, Product::class, 'category_id', 'product_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }


    public static function getNestedDropdown()
    {
        // Carica le categorie radice con i figli ricorsivi già eager loaded
        $categories = self::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('order')
            ->get();

        return self::buildDropdown($categories);
    }

    protected static function buildDropdown($categories, $prefix = '')
    {
        $dropdown = [];
        foreach ($categories as $category) {
            $dropdown[$category->id] = $prefix . $category->name;
            if ($category->childrenRecursive->isNotEmpty()) {
                $dropdown += self::buildDropdown($category->childrenRecursive, $prefix . '-');
            }
        }
        return $dropdown;
    }

    public function getFullPathAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_path . '/' . $this->slug;
        }
        return $this->slug;
    }

}
