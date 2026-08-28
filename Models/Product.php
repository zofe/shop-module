<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name', 'slug', 'sku', 'sku_type', 'type',
        'category_id', 'description', 'image_path',
    ];


    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function getIsServiceAttribute()
    {
        return $this->type === 'service_item';
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getFullPathAttribute()
    {
        return $this->category->full_path . '/' . $this->slug;
    }

    public function getThumbPathAttribute(): ?string
    {
        if (!$this->image_path) return null;
        return \Illuminate\Support\Str::beforeLast($this->image_path, '.jpg') . '_thumb.jpg';
    }
}
